<?php

namespace App\Services;

use App\Models\Vaccine;
use App\Repositories\Contracts\VaccineRepositoryInterface;
use App\Repositories\Contracts\StockTransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VaccineService
{
    protected $vaccineRepository;
    protected $stockTransactionRepository;

    public function __construct(
        VaccineRepositoryInterface $vaccineRepository,
        StockTransactionRepositoryInterface $stockTransactionRepository
    ) {
        $this->vaccineRepository = $vaccineRepository;
        $this->stockTransactionRepository = $stockTransactionRepository;
    }

    /**
     * Create a new vaccine
     *
     * @param array $data
     * @return Vaccine
     */
    public function createVaccine(array $data): Vaccine
    {
        return DB::transaction(function () use ($data) {
            // Extract stock fields
            $initialStock = $data['initial_stock'] ?? 0;
            $minStock = $data['min_stock'] ?? 10;
            
            // Prepare vaccine data with stock values
            $vaccineData = array_merge($data, [
                'current_stock' => $initialStock,
                'min_stock' => $minStock,
            ]);
            
            $vaccine = $this->vaccineRepository->create($vaccineData);

            // If initial stock > 0, record an initial stock transaction
            if ($initialStock > 0) {
                $this->stockTransactionRepository->create([
                    'vaccine_id' => $vaccine->id,
                    'transaction_type' => 'in',
                    'quantity' => $initialStock,
                    'previous_stock' => 0,
                    'new_stock' => $initialStock,
                    'reason' => 'Initial stock received',
                ]);
            }

            Log::info('Vaccine created', [
                'vaccine_id' => $vaccine->id,
                'vaccine_name' => $vaccine->name,
                'initial_stock' => $initialStock,
                'min_stock' => $minStock,
            ]);

            return $vaccine;
        });
    }

    /**
     * Update vaccine
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateVaccine(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $vaccine = $this->vaccineRepository->find($id);
            if (!$vaccine) {
                return false;
            }

            $oldStock = $vaccine->current_stock;
            $newStock = $data['current_stock'] ?? $oldStock;
            $stockChanged = $oldStock !== $newStock;

            $result = $this->vaccineRepository->update($id, $data);

            if ($result && $stockChanged) {
                $difference = $newStock - $oldStock;
                if ($difference !== 0) {
                    $transactionType = $difference > 0 ? 'in' : 'out';
                    $quantity = abs($difference);
                    
                    $this->stockTransactionRepository->create([
                        'vaccine_id' => $id,
                        'transaction_type' => $transactionType,
                        'quantity' => $quantity,
                        'previous_stock' => $oldStock,
                        'new_stock' => $newStock,
                        'reason' => 'Stock adjusted during vaccine update',
                    ]);
                }

                Log::info('Vaccine updated', [
                    'vaccine_id' => $id,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'stock_change' => $difference ?? 0,
                ]);
            }

            return $result;
        });
    }

    /**
     * Delete vaccine
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteVaccine(int $id): bool
    {
        $vaccine = $this->vaccineRepository->find($id);

        if (!$vaccine) {
            throw new \Exception('Vaccine not found');
        }

        // Check if vaccine is used in immunizations
        if ($vaccine->immunizations()->count() > 0) {
            throw new \Exception('Cannot delete vaccine that has been used in immunizations');
        }

        return DB::transaction(function () use ($vaccine) {
            $result = $this->vaccineRepository->delete($vaccine->id);

            if ($result) {
                Log::info('Vaccine deleted', [
                    'vaccine_id' => $vaccine->id,
                    'vaccine_name' => $vaccine->name,
                ]);
            }

            return $result;
        });
    }

    /**
     * Add stock to vaccine
     *
     * @param int $vaccineId
     * @param int $quantity
     * @param string $batchNumber
     * @param string|null $expiryDate
     * @param string|null $notes
     * @return bool
     */
    public function addStock(
        int $vaccineId,
        int $quantity,
        string $batchNumber,
        ?string $expiryDate = null,
        ?string $notes = null
    ): bool {
        return DB::transaction(function () use ($vaccineId, $quantity, $batchNumber, $expiryDate, $notes) {
            // Get current stock before update
            $vaccine = $this->vaccineRepository->find($vaccineId);
            $previousStock = $vaccine->current_stock;
            $newStock = $previousStock + $quantity;
            
            // Update vaccine stock
            $this->vaccineRepository->updateStock($vaccineId, $quantity, 'in');

            // Record transaction
            $this->stockTransactionRepository->create([
                'vaccine_id' => $vaccineId,
                'transaction_type' => 'in',
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $notes ?? 'Stock added',
            ]);

            Log::info('Vaccine stock added', [
                'vaccine_id' => $vaccineId,
                'quantity' => $quantity,
                'batch_number' => $batchNumber,
            ]);

            return true;
        });
    }

    /**
     * Reduce stock (when vaccine is administered)
     *
     * @param int $vaccineId
     * @param int $quantity
     * @param string|null $notes
     * @return bool
     * @throws \Exception
     */
    public function reduceStock(int $vaccineId, int $quantity = 1, ?string $notes = null): bool
    {
        $vaccine = $this->vaccineRepository->find($vaccineId);

        if (!$vaccine) {
            throw new \Exception('Vaccine not found');
        }

        if ($vaccine->current_stock < $quantity) {
            throw new \Exception('Insufficient stock available');
        }

        return DB::transaction(function () use ($vaccineId, $quantity, $notes) {
            // Get current stock before update
            $vaccine = $this->vaccineRepository->find($vaccineId);
            $previousStock = $vaccine->current_stock;
            $newStock = $previousStock - $quantity;
            
            // Update vaccine stock
            $this->vaccineRepository->updateStock($vaccineId, $quantity, 'out');

            // Record transaction
            $this->stockTransactionRepository->create([
                'vaccine_id' => $vaccineId,
                'transaction_type' => 'out',
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $notes ?? 'Vaccine administered',
            ]);

            Log::info('Vaccine stock reduced', [
                'vaccine_id' => $vaccineId,
                'quantity' => $quantity,
            ]);

            return true;
        });
    }

    /**
     * Remove stock (alias for reduceStock)
     *
     * @param int $vaccineId
     * @param int $quantity
     * @param string|null $notes
     * @return bool
     * @throws \Exception
     */
    public function removeStock(int $vaccineId, int $quantity, ?string $notes = null): bool
    {
        return $this->reduceStock($vaccineId, $quantity, $notes);
    }

    /**
     * Get vaccines that are expiring soon
     *
     * @param int $days
     * @return \Illuminate\Support\Collection
     */
    public function getExpiringVaccines(int $days = 30): \Illuminate\Support\Collection
    {
        return $this->vaccineRepository->getExpiring($days);
    }

    /**
     * Get low stock vaccines
     *
     * @param int $threshold
     * @return \Illuminate\Support\Collection
     */
    public function getLowStockVaccines(int $threshold = 10): \Illuminate\Support\Collection
    {
        return $this->vaccineRepository->getLowStock($threshold);
    }

    /**
     * Get out of stock vaccines
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOutOfStockVaccines(): \Illuminate\Support\Collection
    {
        return $this->vaccineRepository->getOutOfStock();
    }

    /**
     * Get vaccine inventory alerts
     *
     * @return array
     */
    public function getInventoryAlerts(): array
    {
        return [
            'expiring_soon' => $this->getExpiringVaccines(30),
            'low_stock' => $this->getLowStockVaccines(10),
            'out_of_stock' => $this->getOutOfStockVaccines(),
        ];
    }

    /**
     * Get inventory statistics
     *
     * @return array
     */
    public function getInventoryStats(): array
    {
        $total = $this->vaccineRepository->all()->count();
        $inStock = $this->vaccineRepository->all()->filter(function ($vaccine) {
            return $vaccine->current_stock > ($vaccine->min_stock ?? 10);
        })->count();
        $lowStock = $this->getLowStockVaccines(10)->count();
        $outOfStock = $this->getOutOfStockVaccines()->count();

        return [
            'total' => $total,
            'in_stock' => $inStock,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
        ];
    }

    /**
     * Check if vaccine needs reordering
     *
     * @param int $vaccineId
     * @param int $threshold
     * @return bool
     */
    public function needsReordering(int $vaccineId, int $threshold = 10): bool
    {
        $vaccine = $this->vaccineRepository->find($vaccineId);
        return $vaccine && $vaccine->current_stock <= $threshold;
    }

    /**
     * Get vaccine statistics
     *
     * @param int $vaccineId
     * @return array
     */
    public function getVaccineStatistics(int $vaccineId): array
    {
        $vaccine = $this->vaccineRepository->findWithRelations($vaccineId, [
            'immunizations',
            'childImmunizations',
            'stockTransactions'
        ]);

        if (!$vaccine) {
            return [];
        }

        $stockIn = $this->stockTransactionRepository->getByVaccine($vaccineId)
            ->where('type', 'in')
            ->sum('quantity');

        $stockOut = $this->stockTransactionRepository->getByVaccine($vaccineId)
            ->where('type', 'out')
            ->sum('quantity');

        return [
            'current_stock' => $vaccine->current_stock,
            'total_stock_in' => $stockIn,
            'total_stock_out' => $stockOut,
            'scheduled_immunizations' => $vaccine->immunizations()->where('status', 'Upcoming')->count(),
            'completed_immunizations' => $vaccine->childImmunizations()->count(),
            'is_expiring_soon' => $vaccine->expiry_date && Carbon::parse($vaccine->expiry_date)->lte(Carbon::now()->addDays(30)),
            'days_until_expiry' => $vaccine->expiry_date ? Carbon::now()->diffInDays(Carbon::parse($vaccine->expiry_date), false) : null,
        ];
    }
}

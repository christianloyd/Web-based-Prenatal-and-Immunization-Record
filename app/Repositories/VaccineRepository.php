<?php

namespace App\Repositories;

use App\Models\Vaccine;
use App\Repositories\Contracts\VaccineRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class VaccineRepository implements VaccineRepositoryInterface
{
    /**
     * @var Vaccine
     */
    protected $model;

    /**
     * VaccineRepository constructor
     */
    public function __construct(Vaccine $model)
    {
        $this->model = $model;
    }

    /**
     * Get all vaccines
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find vaccine by ID
     *
     * @param int $id
     * @return Vaccine|null
     */
    public function find(int $id): ?Vaccine
    {
        return $this->model->find($id);
    }

    /**
     * Find vaccine with relationships
     *
     * @param int $id
     * @param array $relations
     * @return Vaccine|null
     */
    public function findWithRelations(int $id, array $relations = []): ?Vaccine
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Create new vaccine
     *
     * @param array $data
     * @return Vaccine
     */
    public function create(array $data): Vaccine
    {
        return $this->model->create($data);
    }

    /**
     * Update vaccine
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $vaccine = $this->find($id);

        if (!$vaccine) {
            return false;
        }

        return $vaccine->update($data);
    }

    /**
     * Delete vaccine
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $vaccine = $this->find($id);

        if (!$vaccine) {
            return false;
        }

        return $vaccine->delete();
    }

    /**
     * Get paginated vaccines
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('name')->paginate($perPage);
    }

    /**
     * Get active vaccines (not expired, in stock)
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->model->where('current_stock', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', Carbon::now());
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Get expiring vaccines within specified days
     *
     * @param int $days
     * @return Collection
     */
    public function getExpiring(int $days = 30): Collection
    {
        $expiryDate = Carbon::now()->addDays($days);

        return $this->model->whereBetween('expiry_date', [Carbon::now(), $expiryDate])
            ->where('current_stock', '>', 0)
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Get low stock vaccines
     *
     * @param int $threshold
     * @return Collection
     */
    public function getLowStock(int $threshold = 10): Collection
    {
        return $this->model->where('current_stock', '>', 0)
            ->where('current_stock', '<=', $threshold)
            ->orderBy('current_stock')
            ->get();
    }

    /**
     * Get out of stock vaccines
     *
     * @return Collection
     */
    public function getOutOfStock(): Collection
    {
        return $this->model->where('current_stock', 0)
            ->orderBy('name')
            ->get();
    }

    /**
     * Search vaccines by name or vaccine code
     *
     * @param string $term
     * @return Collection
     */
    public function search(string $term): Collection
    {
        return $this->model->where('name', 'LIKE', "%{$term}%")
            ->orWhere('vaccine_code', 'LIKE', "%{$term}%")
            ->orWhere('disease_target', 'LIKE', "%{$term}%")
            ->get();
    }

    /**
     * Update vaccine stock
     *
     * @param int $id
     * @param int $quantity
     * @param string $type (in/out)
     * @return bool
     */
    public function updateStock(int $id, int $quantity, string $type = 'in'): bool
    {
        $vaccine = $this->find($id);

        if (!$vaccine) {
            return false;
        }

        if ($type === 'in') {
            $vaccine->current_stock += $quantity;
        } else {
            $vaccine->current_stock -= $quantity;

            // Prevent negative stock
            if ($vaccine->current_stock < 0) {
                $vaccine->current_stock = 0;
            }
        }

        return $vaccine->save();
    }

    /**
     * Get vaccines by disease
     *
     * @param string $disease
     * @return Collection
     */
    public function getByDisease(string $disease): Collection
    {
        return $this->model->where('disease_target', 'LIKE', "%{$disease}%")
            ->orderBy('name')
            ->get();
    }
}

<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendVaccinationReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    protected $phoneNumber;
    protected $childName;
    protected $vaccineName;
    protected $scheduleDate;
    protected $motherName;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $phoneNumber,
        string $childName,
        string $vaccineName,
        string $scheduleDate,
        ?string $motherName = null
    ) {
        $this->phoneNumber = $phoneNumber;
        $this->childName = $childName;
        $this->vaccineName = $vaccineName;
        $this->scheduleDate = $scheduleDate;
        $this->motherName = $motherName;
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        try {
            Log::info('Processing vaccination reminder job', [
                'phone' => $this->phoneNumber,
                'child' => $this->childName,
                'vaccine' => $this->vaccineName
            ]);

            $result = $smsService->sendVaccinationReminder(
                $this->phoneNumber,
                $this->childName,
                $this->vaccineName,
                $this->scheduleDate,
                $this->motherName
            );

            if (!$result['success']) {
                Log::warning('Vaccination reminder job completed but sending failed', [
                    'phone' => $this->phoneNumber,
                    'result' => $result
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Vaccination reminder job failed', [
                'phone' => $this->phoneNumber,
                'child' => $this->childName,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Vaccination reminder job permanently failed', [
            'phone' => $this->phoneNumber,
            'child' => $this->childName,
            'vaccine' => $this->vaccineName,
            'error' => $exception->getMessage()
        ]);
    }
}

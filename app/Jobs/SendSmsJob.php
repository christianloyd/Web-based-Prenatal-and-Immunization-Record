<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
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

    /**
     * The phone number to send SMS to
     *
     * @var string
     */
    protected $phoneNumber;

    /**
     * The SMS message content
     *
     * @var string
     */
    protected $message;

    /**
     * The SMS type
     *
     * @var string
     */
    protected $type;

    /**
     * Recipient name
     *
     * @var string|null
     */
    protected $recipientName;

    /**
     * Related model type
     *
     * @var string|null
     */
    protected $relatedType;

    /**
     * Related model ID
     *
     * @var int|null
     */
    protected $relatedId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $phoneNumber,
        string $message,
        string $type = 'general',
        ?string $recipientName = null,
        ?string $relatedType = null,
        ?int $relatedId = null
    ) {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->type = $type;
        $this->recipientName = $recipientName;
        $this->relatedType = $relatedType;
        $this->relatedId = $relatedId;
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        try {
            Log::info('Processing SMS job', [
                'phone' => $this->phoneNumber,
                'type' => $this->type,
                'attempt' => $this->attempts()
            ]);

            $result = $smsService->sendSms(
                $this->phoneNumber,
                $this->message,
                $this->type,
                $this->recipientName,
                $this->relatedType,
                $this->relatedId
            );

            if (!$result['success']) {
                Log::warning('SMS job completed but sending failed', [
                    'phone' => $this->phoneNumber,
                    'result' => $result
                ]);
            }
        } catch (\Exception $e) {
            Log::error('SMS job failed', [
                'phone' => $this->phoneNumber,
                'type' => $this->type,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SMS job permanently failed after all retries', [
            'phone' => $this->phoneNumber,
            'type' => $this->type,
            'message' => $this->message,
            'error' => $exception->getMessage()
        ]);

        // Optionally notify administrators about permanent failure
        // You could create a notification here
    }
}

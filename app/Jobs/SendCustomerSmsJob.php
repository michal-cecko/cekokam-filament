<?php

namespace App\Jobs;

use App\Enum\Customer\CustomerSmsType;
use App\Models\Customer\Customer;
use App\Services\Customers\CustomerSmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendCustomerSmsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    /**
     * @param  array<int>  $customerIds
     */
    public function __construct(
        public array $customerIds,
        public string $type,
        public ?string $content = null,
    ) {}

    public function handle(): void
    {
        $customers = Customer::query()->whereIn('id', $this->customerIds)->get();

        if ($customers->isEmpty()) {
            return;
        }

        if ($this->type === CustomerSmsType::PAYMENT_REQUEST->value) {
            CustomerSmsService::sendPaymentRequestSms($customers);

            return;
        }

        CustomerSmsService::sendCustomSms($customers, (string) $this->content);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendCustomerSmsJob failed', [
            'type' => $this->type,
            'customer_ids' => $this->customerIds,
            'error' => $exception->getMessage(),
        ]);
    }
}

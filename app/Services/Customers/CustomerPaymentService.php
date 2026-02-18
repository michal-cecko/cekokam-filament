<?php

namespace App\Services\Customers;

use App\Enum\Customer\CustomerPaymentStatus;
use App\Enum\Customer\CustomerStatus;
use App\Models\Customer\Customer;
use App\Models\Customer\Payment\CustomerPayment;
use Illuminate\Support\Facades\Log;

class CustomerPaymentService
{
    public static function processLatestPayments(): void
    {
        $payments = PaymentMailService::getLatestPayments();

        if (empty($payments)) {
            return;
        }

        foreach ($payments as $payment) {
            $customer = Customer::where('iban', 'LIKE', '%'.$payment['iban'])->first();
            if (! $customer) {
                Log::error("Customer with IBAN {$payment['iban']} not found. Skipping payment with amount {$payment['amount']} from date {$payment['received_at']?->format('Y-m-d H:i:s')}");

                continue;
            }

            ['status' => $status, 'note' => $note] = self::getCustomerPaymentStatus($payment['amount'], $customer);

            CustomerPayment::firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'received_at' => $payment['received_at'],
                    'amount_paid' => $payment['amount'],
                    'iban' => $customer->iban,
                ],
                [
                    'amount_expected' => $customer->total_price,
                    'customer_name' => $customer->name,
                    'status' => $status,
                    'note' => $note,
                ]
            );

            if (in_array($status, [CustomerPaymentStatus::OK, CustomerPaymentStatus::TOO_MUCH])) {
                Log::info("Changing customer id: {$customer->id} status to PAID.");
                $customer->status = CustomerStatus::PAID;
                $customer->save();
            }
        }
    }

    public static function getCustomerPaymentStatus(float $receivedAmount, Customer $customer): array
    {
        // Total amount expected from the customer
        $amountExpected = $customer->total_price;

        $currentPeriod = BillingPeriodService::getCurrentPeriod();

        $currentPeriodPayments = CustomerPayment::query()
            ->where('customer_id', $customer->id)
            ->whereBetween('received_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->sum('amount_paid');

        // Total paid amount including the current payment
        $totalPaid = $currentPeriodPayments + $receivedAmount;

        $return = [
            'status' => CustomerPaymentStatus::REDUNDANT,
            'note' => null,
        ];

        // Determine the payment status
        if ($totalPaid < $amountExpected) {
            $return['status'] = CustomerPaymentStatus::NOT_SUFFICIENT;
            $return['note'] = "Prijaté {$receivedAmount}€, ale očakávaná suma je {$amountExpected}€";
        } elseif ($totalPaid == $amountExpected) {
            $return['status'] = CustomerPaymentStatus::OK;
        } elseif ($receivedAmount > $amountExpected) {
            $return['status'] = CustomerPaymentStatus::TOO_MUCH;
            $return['note'] = "Prijaté {$receivedAmount}€, ale očakávaná suma je {$amountExpected}€";
        }

        return $return;
    }
}

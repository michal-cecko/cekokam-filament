<?php

namespace App\Services\Customers;

use App\Enum\Customer\CustomerStatus;
use App\Models\Customer\Customer;
use App\Services\Sms\SmsSenderService;
use BulkGate\Sdk\ApiException;
use BulkGate\Sdk\SenderException;
use BulkGate\Sdk\TypeError;
use Illuminate\Support\Collection;

class CustomerSmsService
{
    /**
     * @throws SenderException
     * @throws TypeError
     * @throws ApiException
     */
    public static function sendPaymentRequestSms(Customer|Collection $customers): int
    {
        if ($customers instanceof Customer) {
            $customers = new Collection([$customers]);
        }

        $smsToSend = [];
        foreach ($customers as $customer) {
            if ($customer->status !== CustomerStatus::UNPAID) {
                continue;
            }

            $content = self::getCustomerPaymentRequestSmsContent($customer);

            foreach ($customer->phone as $phone) {
                $smsToSend[] = [
                    'content' => $content,
                    'number' => $phone,
                ];
            }
        }

        $smsClient = new SmsSenderService;
        $smsClient->send($smsToSend);

        return count($smsToSend);
    }

    public static function getCustomerPaymentRequestSmsContent(Customer $customer): string
    {
        return "SATELITNA SLUZBA\n\nDobry den {$customer->name} - {$customer->city?->name}.\nPosielam Vam informacie k uhrade sumy za satelitne sluzby za obdobie:\n\n{$customer->subscription_date_string}\n\nSuma: {$customer->total_price},- EUR\n\nCislo uctu (IBAN):\n {$customer->iban}\n\nUPOZORNENIE: Pre klientov, ktori budu realizovat platbu z uctu vedeneho v inej banke ako Tatra banka, je potrebne vypnut okamzitu platbu alebo nastavit datum realizacie platby na nasledujuci den.\nV opacnom pripade platba neprejde a bude Vam vratena spat na Vas ucet.\n\nV pripade otazok ma prosim kontaktujte na tel. cisle\n0911269686";
    }

    /**
     * @throws SenderException
     * @throws TypeError
     * @throws ApiException
     */
    public static function sendCustomSms(Customer|Collection $customers, string $content): int
    {
        if ($customers instanceof Customer) {
            $customers = new Collection([$customers]);
        }

        $smsToSend = [];
        foreach ($customers as $customer) {
            foreach ($customer->phone as $phone) {
                $smsToSend[] = [
                    'content' => $content,
                    'number' => $phone,
                ];
            }
        }

        $smsClient = new SmsSenderService;
        $smsClient->send($smsToSend);

        return count($smsToSend);
    }
}

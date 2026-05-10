<?php

namespace App\Services\Customers;

use App\Enum\Customer\CustomerStatus;
use App\Models\Customer\Customer;
use App\Models\Setting;
use App\Services\Sms\SmsSenderService;
use BulkGate\Sdk\ApiException;
use BulkGate\Sdk\SenderException;
use BulkGate\Sdk\TypeError;
use Illuminate\Support\Collection;

class CustomerSmsService
{
    public const PAYMENT_REQUEST_TEMPLATE_KEY = 'payment_request_sms_template';

    public const DEFAULT_PAYMENT_REQUEST_TEMPLATE = "SATELITNA SLUZBA\n\nDobry den {name} - {city}.\nPosielam Vam informacie k uhrade sumy za satelitne sluzby za obdobie:\n\n{period}\n\nSuma: {amount},- EUR\n\nCislo uctu (IBAN):\n {iban}\n\nUPOZORNENIE: Pre klientov, ktori budu realizovat platbu z uctu vedeneho v inej banke ako Tatra banka, je potrebne vypnut okamzitu platbu alebo nastavit datum realizacie platby na nasledujuci den.\nV opacnom pripade platba neprejde a bude Vam vratena spat na Vas ucet.\n\nV pripade otazok ma prosim kontaktujte na tel. cisle\n0911269686";

    /**
     * @return array<string, string>
     */
    public static function paymentRequestVariables(): array
    {
        return [
            '{name}' => 'Meno zákazníka',
            '{city}' => 'Mesto zákazníka',
            '{period}' => 'Obdobie predplatného (napr. „01.01.2026 - 31.01.2026")',
            '{amount}' => 'Celková suma na úhradu (€)',
            '{iban}' => 'IBAN serverového účtu zákazníka',
        ];
    }

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

        if (empty($smsToSend)) {
            return 0;
        }

        $smsClient = new SmsSenderService;
        $smsClient->send($smsToSend);

        return count($smsToSend);
    }

    public static function getCustomerPaymentRequestSmsContent(Customer $customer): string
    {
        $template = Setting::get(self::PAYMENT_REQUEST_TEMPLATE_KEY, self::DEFAULT_PAYMENT_REQUEST_TEMPLATE);

        return strtr($template, [
            '{name}' => (string) $customer->name,
            '{city}' => (string) ($customer->city?->name ?? ''),
            '{period}' => (string) $customer->subscription_date_string,
            '{amount}' => (string) $customer->total_price,
            '{iban}' => (string) ($customer->server?->iban ?? ''),
        ]);
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

        if (empty($smsToSend)) {
            return 0;
        }

        $smsClient = new SmsSenderService;
        $smsClient->send($smsToSend);

        return count($smsToSend);
    }
}

<?php

namespace App\Services\Customers;

use App\Services\Imap\ImapClient;
use Carbon\Carbon;

class PaymentMailService
{
    public static function getLatestPayments(): array
    {
        $client = new ImapClient;

        $paymentEmails = $client->getInbox('ALL ON "'.date('j F Y').'" BODY "zvyseny" FROM "b-mail@tatrabanka.sk"');

        if (empty($paymentEmails)) {
            return [];
        }

        $payments = [];

        foreach ($paymentEmails as $mail) {
            $dateReceived = self::extractDateReceived($mail);
            if (! $dateReceived) {
                continue;
            }

            $receivedAmount = self::extractReceivedAmount($mail);
            $accountNumber = self::extractAccountNumber($mail);

            if (! $receivedAmount || ! $accountNumber) {
                continue;
            }

            $payments[] = [
                'received_at' => $dateReceived,
                'amount' => $receivedAmount,
                'iban' => $accountNumber,
            ];
        }

        $client->close();

        return $payments;
    }

    /**
     * Extract the date of payment from the email body.
     */
    private static function extractDateReceived(string $body): ?Carbon
    {
        $patterns = ['Vazeny klient,', 'bol zostatok Vasho uctu'];
        $parts = self::multiExplode($patterns, $body);

        if (! isset($parts[1])) {
            return null;
        }

        try {
            return Carbon::parse(trim($parts[1]));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract the received amount from the email body.
     */
    private static function extractReceivedAmount(string $body): ?float
    {
        $patterns = ['zvyseny o', 'EUR. uctovny zostatok'];
        $parts = self::multiExplode($patterns, $body);

        if (! isset($parts[1])) {
            return null;
        }

        return floatval(str_replace(',', '.', $parts[1]));
    }

    /**
     * Extract the account number of the recipient from the email body.
     */
    private static function extractAccountNumber(string $body): ?string
    {
        $patterns = ['Popis transakcie:', 'MAT Principal'];
        $parts = self::multiExplode($patterns, $body);

        if (! isset($parts[1]) || count($parts) !== 3 || ! str_contains($body, 'MAT Principal')) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', trim($parts[1]));
    }

    /**
     * Explode a string by multiple delimiters.
     */
    private static function multiExplode(array $delimiters, string $string): array
    {
        $pattern = '/'.implode('|', array_map('preg_quote', $delimiters)).'/';

        return preg_split($pattern, $string);
    }
}

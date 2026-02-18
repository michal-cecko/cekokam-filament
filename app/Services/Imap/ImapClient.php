<?php

namespace App\Services\Imap;

use Exception;
use IMAP\Connection;

class ImapClient
{
    private false|Connection $client;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $hostname = config('imap.hostname');
        $username = config('imap.username');
        $password = config('imap.password');

        $this->client = @imap_open($hostname, $username, $password);

        if ($this->client === false) {
            throw new Exception('Cannot connect to IMAP mail domain. Error: '.imap_last_error());
        }

        $status = imap_check($this->client);
        if (! $status) {
            throw new Exception('Mailbox check failed. Error: '.imap_last_error());
        }
    }

    public function getInbox(string $criteria, $flags = SE_FREE): bool|array
    {
        $results = imap_search($this->client, $criteria, $flags);

        if ($results === false) {
            return false;
        }

        rsort($results);

        $finalResults = [];
        foreach ($results as $result) {
            $content = $this->getEmailContent($result);
            if ($content !== false) {
                $finalResults[] = $content;
            }
        }

        return $finalResults;
    }

    private function getEmailContent($email): string|bool
    {
        $structure = imap_fetchstructure($this->client, $email);

        if (! $structure) {
            return false;
        }

        if (! isset($structure->parts)) {
            // Single-part message
            return $this->decodeContent(
                imap_body($this->client, $email),
                $structure->encoding
            );
        }

        // Multi-part message: prioritize plain text
        foreach ($structure->parts as $index => $part) {
            if ($part->subtype === 'PLAIN') {
                $body = imap_fetchbody($this->client, $email, $index + 1);

                return $this->decodeContent($body, $part->encoding);
            }
        }

        // Fallback: fetch the first part
        $body = imap_fetchbody($this->client, $email, 1);

        return $this->decodeContent($body, $structure->parts[0]->encoding);
    }

    private function decodeContent(string $content, int $encoding): string
    {
        return match ($encoding) {
            ENCBASE64 => base64_decode($content),
            ENCQUOTEDPRINTABLE => quoted_printable_decode($content),
            default => $content,
        };
    }

    public function close(): void
    {
        imap_expunge($this->client);
        imap_close($this->client);
    }
}

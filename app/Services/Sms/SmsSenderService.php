<?php

namespace App\Services\Sms;

use BulkGate\Sdk\ApiException;
use BulkGate\Sdk\Configurator\SmsConfigurator;
use BulkGate\Sdk\Connection\ConnectionStream;
use BulkGate\Sdk\Message\Bulk;
use BulkGate\Sdk\Message\Sms;
use BulkGate\Sdk\MessageSender;
use BulkGate\Sdk\SenderException;
use BulkGate\Sdk\TypeError;

class SmsSenderService
{
    private ConnectionStream $client;

    private MessageSender $sender;

    public function __construct()
    {
        $this->client = new ConnectionStream(
            application_id: config('sms.bulkgate.application_id'),
            application_token: config('sms.bulkgate.application_token')
        );
        $this->sender = new MessageSender($this->client);
        $configurator = new SmsConfigurator;
        $configurator->mobileConnect(key: config('sms.bulkgate.application_mobile_connect_key'));
        $configurator->unicode(enabled: false);
        $this->sender->addSenderConfigurator($configurator);
    }

    /**
     * @throws SenderException
     * @throws TypeError
     * @throws ApiException
     */
    public function send(iterable $numbersWithMessages): void
    {
        $message = new Bulk;

        foreach ($numbersWithMessages as $messageData) {
            $message[] = new Sms($messageData['number'], $messageData['content']);
        }

        $this->sender->send($message);
    }
}

<?php

namespace App\Console\Commands;

use App\Services\AccountSubscription\AccountSubscriptionService;
use Exception;
use Illuminate\Console\Command;

class SendAccountExpirationNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:account-expiry-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        AccountSubscriptionService::sendExpiryNotifications();
    }
}

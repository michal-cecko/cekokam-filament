<?php

namespace App\Console\Commands;

use App\Models\Customer\Customer;
use App\Models\Customer\CustomerService;
use App\Models\Service\AccountSubscription;
use App\Models\User;
use App\Notifications\ArchiveAccountExpiredNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}

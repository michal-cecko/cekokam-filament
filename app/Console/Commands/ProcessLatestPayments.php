<?php

namespace App\Console\Commands;

use App\Services\Customers\CustomerPaymentService;
use Illuminate\Console\Command;

class ProcessLatestPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-latest-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        CustomerPaymentService::processLatestPayments();
    }
}

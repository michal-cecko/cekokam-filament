<?php

namespace App\Console\Commands;

use App\Services\Stream\StreamService;
use Illuminate\Console\Command;

class PruneDownloadedStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-downloaded-streams';

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
        StreamService::pruneOldTsFiles();
    }
}

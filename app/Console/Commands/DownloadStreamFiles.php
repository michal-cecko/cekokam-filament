<?php

namespace App\Console\Commands;

use App\Models\ChannelStream;
use App\Services\Stream\StreamService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DownloadStreamFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-stream-files {slug?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download stream files, optionally for a specific stream ID.';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        $slug = $this->argument('slug');

        $streams = ChannelStream::where('is_active', true)
            ->when(! empty($slug), function ($query) use ($slug) {
                return $query->where('slug', $slug);
            })
            ->get();

        foreach ($streams as $stream) {
            try {
                $requestCount = 0;
                $start = microtime(true);

                StreamService::downloadLatestStreamFiles($stream, $requestCount);

                $end = microtime(true);
                $runtime = round($end - $start, 2);

                Log::info("{$stream->name} | STREAM DOWNLOAD FINISHED | Requests: $requestCount | Runtime: $runtime seconds");

                Log::info('');
                Log::info('');
                Log::info('');
            } catch (Exception $e) {
                Log::info("{$stream->name} | ERROR | ".$e->getMessage());
            }
        }
    }
}

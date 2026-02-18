<?php

namespace App\Services\Stream;

use App\Models\ChannelStream;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StreamService
{
    const PRESERVE_COUNT = 50;

    /**
     * @throws Exception
     */
    public static function downloadLatestStreamFiles(ChannelStream $stream, &$requestCount): array
    {
        $response = Http::get($stream->source);
        $requestCount++;

        if (! $response->ok()) {
            throw new Exception("Failed to download m3u8 file from {$stream->source}, response: {$response->body()}");
        }

        $m3u8Content = $response->body();
        $lines = explode("\n", $m3u8Content);

        $folderPath = "streams/{$stream->slug}";

        $timestampFiles = [];
        $extinfNumber = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line && ! str_starts_with($line, '#')) {
                $timestampFiles[] = $line;
            } elseif (str_starts_with($line, '#EXTINF:')) {
                $extinfNumber = (int) explode(':', $line)[1];
            }
        }

        if (empty($timestampFiles)) {
            throw new Exception('No timestamp files found in m3u8 content.');
        }

        $renamedFiles = [];
        foreach ($timestampFiles as $timestampFile) {
            $timestampName = pathinfo($timestampFile, PATHINFO_FILENAME);
            $tsFolderPath = "{$folderPath}/ts/{$timestampName}";

            if (Storage::disk('public')->exists($tsFolderPath) && ! empty($files = Storage::disk('public')->files($tsFolderPath))) {
                $renamedFiles[$timestampName] = $files[0];

                continue;
            }

            Storage::disk('public')->makeDirectory($tsFolderPath);

            $fileUrl = dirname($stream->source).'/'.$timestampFile;

            // Define the path where the file will be saved
            $hashedName = md5($timestampFile).'.ts';
            $filePath = Storage::disk('public')->path("{$tsFolderPath}/{$hashedName}");
            $start = microtime(true);
            $curlCommand = "curl -s --max-time 30 -o $filePath -w \"%{http_code}\" $fileUrl";
            exec($curlCommand, $output, $returnVar);
            $requestCount++;
            $end = microtime(true);
            $runtime = round($end - $start, 2);

            $httpStatusCode = intval(end($output));

            if ($returnVar !== 0) {
                // Handle shell-level errors (e.g., curl failed to run properly)
                Log::info("{$stream->name} | FAILED DOWNLOAD {$timestampName} TS | Runtime: $runtime | Shell Error: $returnVar");

                continue;
            }

            if ($httpStatusCode !== 200) {
                // Handle HTTP errors (e.g., server returned 500)
                Log::info("{$stream->name} | FAILED DOWNLOAD {$timestampName} TS | Runtime: $runtime | HTTP Status: $httpStatusCode");

                continue;
            }

            // Validate file existence and size as a fallback
            if (! file_exists($filePath)) {
                Log::info("{$stream->name} | FAILED DOWNLOAD {$timestampName} TS | Runtime: $runtime | Error: File not downloaded.");

                continue;
            }

            // Validate file existence and size as a fallback
            if (filesize($filePath) === 0) {
                Log::info("{$stream->name} | FAILED DOWNLOAD {$timestampName} TS | Runtime: $runtime | Error: File empty.");

                continue;
            }

            // Check if the file contains "Internal Server Error"
            $fileContents = file_get_contents($filePath);
            if (str_contains($fileContents, 'Internal Server Error')) {
                Log::info("{$stream->name} | FAILED DOWNLOAD {$timestampName} TS | Runtime: $runtime | Error: File contains 'Internal Server Error'");
                unlink($filePath); // Optionally delete the invalid file

                continue;
            }

            Log::info("{$stream->name} | NEW TS FILE | {$timestampName} => {$hashedName} | Runtime: $runtime");

            $renamedFiles[$timestampName] = "{$tsFolderPath}/{$hashedName}";
        }

        // Step 5: Create a new m3u8 file with updated file names
        $tvgLogoAndStreamNameLine = "#EXTINF:$extinfNumber tvg-logo=\"{$stream->logo_url}\", {$stream->name}";
        $updatedM3u8Content = [];
        foreach ($lines as $line) {
            $line = trim($line);
            $timestampName = pathinfo($line, PATHINFO_FILENAME);
            if (! empty($tsFile = ($renamedFiles[$timestampName] ?? null))) {
                $tsFileRelativePath = explode('/ts/', $tsFile)[1];
                $updatedM3u8Content[] = "ts/{$tsFileRelativePath}";
            } elseif (str_starts_with($line, '#EXTINF:')) {
                $updatedM3u8Content[] = $tvgLogoAndStreamNameLine;
            } else {
                $updatedM3u8Content[] = $line;
            }
        }

        // Save the updated m3u8 file in the stream folder (not in the 'ts/sequence' folder)
        $newM3u8FileName = "{$folderPath}/stream.m3u8";
        Storage::disk('public')->put($newM3u8FileName, implode("\n", $updatedM3u8Content));

        return [
            'm3u8_file' => Storage::url($newM3u8FileName),
            'timestamp_files' => array_map(fn ($filePath) => Storage::url($filePath), $renamedFiles),
        ];
    }

    public static function pruneOldTsFiles(): void
    {
        try {
            // Get all stream folders
            $streamFolders = Storage::disk('public')->directories('streams');

            if (empty($streamFolders)) {
                throw new Exception("No stream folders found in 'streams' directory.");
            }

            foreach ($streamFolders as $streamFolder) {
                $tsFolderPath = "{$streamFolder}/ts";

                // Get all sequence folders inside the 'ts' directory
                $sequenceFolders = Storage::disk('public')->directories($tsFolderPath);

                if (empty($sequenceFolders)) {
                    // Skip if no sequence folders are found
                    continue;
                }

                // Extract sequence numbers and sort them in descending order
                $sortedFolders = collect($sequenceFolders)->sortByDesc(function ($folder) {
                    // Extract the sequence from the folder name (e.g., streams/<name>/ts/<sequence>)
                    return (int) basename($folder);
                });

                // Keep the three latest sequence folders and prune the rest
                $foldersToDelete = $sortedFolders->slice(self::PRESERVE_COUNT); // Keep first three, delete the rest

                foreach ($foldersToDelete as $folder) {
                    Storage::disk('public')->deleteDirectory($folder);
                }
            }
        } catch (Exception $e) {
            throw new Exception('Error pruning sequence folders: '.$e->getMessage());
        }
    }
}

<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StartImportJob implements ShouldQueue
{
    public function __construct(public string $path) {}

    public function handle()
    {
        $fullPath = Storage::disk('local')->path($this->path);
        $handle = fopen($fullPath, 'r');

        $chunk = [];
        $chunkNumber = 1;

        while (($line = fgets($handle)) !== false) {
            $trim = trim($line);

            if ($trim === '' || str_starts_with($trim, '--') || str_starts_with($trim, '/*')) {
                continue;
            }

            $chunk[] = $line;

            if (count($chunk) === 1000) {
                dispatch(new ProcessChunkJob($chunkNumber, $chunk));
                $chunk = [];
                $chunkNumber++;
            }
        }

        if (!empty($chunk)) {
            dispatch(new ProcessChunkJob($chunkNumber, $chunk));
        }

        fclose($handle);

        Log::info("StartImportJob finished dispatching chunks");
    }
}

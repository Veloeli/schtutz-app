<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImportSqlJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $path) {}

    public function handle()
    {
        $fullPath = Storage::disk('local')->path($this->path);

        $handle = fopen($fullPath, 'r');
        $statement = '';

        while (($line = fgets($handle)) !== false) {
            $trim = trim($line);

            if ($trim === '' || str_starts_with($trim, '--') || str_starts_with($trim, '/*')) {
                continue;
            }

            $statement .= $line;

            if (str_ends_with($trim, ';')) {
                DB::unprepared($statement);
                $statement = '';
            }
        }

        fclose($handle);
    }
}

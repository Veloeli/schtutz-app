<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessChunkJob implements ShouldQueue
{
    public function __construct(
        public int $chunkNumber,
        public array $lines
    ) {}

    public function handle()
    {
        $statement = '';

        foreach ($this->lines as $line) {
            $trim = trim($line);

            $statement .= $line;

            if (str_ends_with($trim, ';')) {
                DB::unprepared($statement);
                $statement = '';
            }
        }

        Log::info("Processed chunk {$this->chunkNumber}");
    }
}

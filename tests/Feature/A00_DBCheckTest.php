<?php

namespace Tests\Feature;

use Tests\TestCase;

class A00_DBCheckTest extends TestCase
{
    public function test_db_config()
    {
        if (! app()->environment('testing')) {
            dd([
                'ERROR' => 'APP_ENV is NOT testing!',
                'env' => app()->environment(),
                'default_connection' => config('database.default'),
                'mysql_database' => config('database.connections.mysql.database'),
                //'sqlite_database' => config('database.connections.sqlite.database'),
            ]);
        }

        // If we reach here, environment is correct.
        $this->assertTrue(true);
    }
}

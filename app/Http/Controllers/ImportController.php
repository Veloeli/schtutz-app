<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\ImportSqlJob;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|extensions:sql,txt',
        ]);

        $stored = $request->file('sql_file')->store('imports', 'local');

        dispatch(new ImportSqlJob($stored));

        return back()->with('success', 'Import started in background.');
    }
}

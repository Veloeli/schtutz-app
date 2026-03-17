<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Security;
use Illuminate\Http\Request;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;

class QuoteController extends Controller
{
    public function index()
    {
        $securities = Security::orderBy('name')->get();

        $quotes = Quote::with('security')
            ->when(request('security_id'), fn($q) =>
                $q->where('security_id', request('security_id'))
            )
            ->when(request('from_date'), fn($q) =>
                $q->whereDate('quote_date', '>=', request('from_date'))
            )
            ->when(request('to_date'), fn($q) =>
                $q->whereDate('quote_date', '<=', request('to_date'))
            )
            ->orderBy('quote_date', 'desc')
            ->paginate(50);

        return view('quotes.index', compact('quotes', 'securities'));
    }

    public function showImportForm()
    {
        $securities = Security::orderBy('name')->get();
        return view('quotes.import', compact('securities'));
    }

    public function import(Request $request)
    {
        if (!$request->security_id && !$request->fixed_date) {
            return back()->withErrors([
                'raw' => 'Please preselect either a security or a date before importing.'
            ]);
        }
        $request->validate([
            'raw' => 'required|string',
            'security_id' => 'nullable|exists:securities,id',
            'fixed_date' => 'nullable|date',
        ]);

        $lines = preg_split('/\r\n|\r|\n/', trim($request->raw));

        $imported = [];

        foreach ($lines as $line) {
            // Split by tab or semicolon or comma
            $cols = preg_split('/[\t;,]+/', trim($line));

            if (count($cols) < 2) {
                continue; // skip invalid rows
            }

            $first = trim($cols[0]);
            $last  = trim($cols[count($cols) - 1]);

            // Determine security_id
            if ($request->security_id) {
                $securityId = $request->security_id;
                $date = parseDateSmart($first);
            } elseif ($request->fixed_date) {
                $date = $request->fixed_date;

                // Try to find security by ticker/isin/identifier
                $security = Security::where('ticker', $first)
                    ->orWhere('isin', $first)
                    ->orWhere('identifier', $first)
                    ->first();

                if (!$security) {
                    continue; // skip unknown security
                }

                $securityId = $security->id;
            } else {
                // No preselection → try to auto-detect
                $security = Security::where('ticker', $first)
                    ->orWhere('isin', $first)
                    ->orWhere('identifier', $first)
                    ->first();

                if ($security) {
                    $securityId = $security->id;
                    $date = null; // must be provided in another column or preselected
                } else {
                    // Assume first column is date
                    $securityId = null;
                    $date = $first;
                }
            }

            // Parse price
            $price = floatval(str_replace(',', '.', $last));

            // Skip if missing required fields
            if (!$securityId || !$date) {
                continue;
            }

            // Create or update quote
            $quote = Quote::updateOrCreate(
                [
                    'security_id' => $securityId,
                    'quote_date' => $date,
                ],
                [
                    'price' => $price,
                ]
            );

            $imported[] = $quote;
        }

        return back()->with('success', count($imported) . ' quotes imported.');
    }
}

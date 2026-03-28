<?php

namespace App\Http\Controllers;

use App\Models\Security;
use App\Models\User;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        $query = Security::with(['team','user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ticker', 'like', "%{$search}%")
                  ->orWhere('isin', 'like', "%{$search}%")
                  ->orWhere('identifier', 'like', "%{$search}%");
            });
        }

        $securities = $query
            ->orderByDesc('is_tracked')
            ->orderBy('name')
            ->paginate(100)
            ->withQueryString();

        return view('securities.index', compact('securities'));
    }

    public function create()
    {
        $user = auth()->user();

        $teams = $user->teamsWithSecurities()->get();

        return view('securities.create', [
            'security' => new Security(),
            'teams'    => $teams
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $data['user_id'] = auth()->id();

        Security::create($data);

        return redirect()->route('securities.index')
            ->with('success', 'Security created successfully.');
    }

    public function edit(Security $security)
    {
        $user = auth()->user();

        $teams = $user->teamsWithSecurities()->get();

        return view('securities.edit', compact('security', 'teams'));
    }

    public function update(Request $request, Security $security)
    {
        $data = $this->validateData($request);

        $security->update($data);

        return redirect()->route('securities.index')
            ->with('success', 'Security updated successfully.');
    }

    public function destroy(Security $security)
    {
        $security->delete();

        return redirect()->route('securities.index')
            ->with('success', 'Security deleted.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'team_id' => 'nullable|exists:teams,id',
            'name' => 'required|string|max:255',
            'isin' => 'nullable|string|max:255',
            'ticker' => 'nullable|string|max:255',
            'identifier' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'asset_class' => 'nullable|string|size:2',
            'currency_id' => 'nullable|exists:securities,id',
            'is_in_use' => 'boolean',
            'is_tracked' => 'boolean',
            'is_hedged' => 'boolean',
            'region' => 'nullable|string|max:255',
            'sector' => 'nullable|string|max:255',
            'strategy' => 'nullable|string|max:255',
            'theme' => 'nullable|string|max:255',
            'option_type' => 'nullable|in:CALL,PUT',
            'option_underlying_id' => 'nullable|exists:securities,id',
            'option_strike' => 'nullable|numeric',
            'option_multiplier' => 'nullable|numeric',
            'option_calculate' => 'boolean',
        ]);
    }
}

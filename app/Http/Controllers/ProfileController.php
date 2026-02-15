<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    
    public function storeDeputy(Request $request)
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'exists:users,email',
                function ($attribute, $value, $fail) {
                    if ($value === auth()->user()->email) {
                        $fail('You cannot assign yourself as a deputy.');
                    }
                },
            ],
        ]);
        

        $deputy = User::where('email', $validated['email'])->first();

        auth()->user()->deputies()->syncWithoutDetaching([$deputy->id]);

        return back()->with('success', 'Stellvertretung hinzugefügt.');
    }

    public function destroyDeputy(User $deputy)
    {
        auth()->user()->deputies()->detach($deputy->id);

        return back()->with('success', 'Stellvertretung entfernt.');
    }

}

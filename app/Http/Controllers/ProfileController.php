<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rollup;

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
        $rootRollups = Rollup::roots()->get();

        return view('profile.edit', [
            'user' => $request->user(),
            'rootRollups' => $rootRollups,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->validate([
            'preferred_root_id' => ['nullable', 'exists:rollups,id'],
        ]);

        $user = $request->user();
        $user->fill($validated);
        $user->preferred_root_id = $request->preferred_root_id;

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

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

        return back()->with('success', 'Deputy attached.');
    }

    public function destroyDeputy(User $deputy)
    {
        auth()->user()->deputies()->detach($deputy->id);

        return back()->with('success', 'Deputy removed.');
    }

}

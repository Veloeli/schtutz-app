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
            'preferred_root_id' => 'nullable|exists:rollups,id',
            'freeze_after'      => 'required|integer|between:1,120',
        ]);

        $user = $request->user();
        $user->fill($validated);
        $user->preferred_root_id = $request->preferred_root_id;
        $user->freeze_after = $request->freeze_after;

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
    
}

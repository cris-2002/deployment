<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\User;
use App\Models\Allergy;
use Illuminate\View\View;
use App\Models\UserAllergy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ProfileUpdateRequest;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {

        $allergies = Allergy::pluck('name', 'id')->all();

        return view('profile.edit', [
            'user' => $request->user(),
            'allergies' => $allergies,
        ]);
    }

    public function allprofileinfo()
    {

        $userId = Auth::id();
        $users = User::with(['roles', 'user_allergies.allergy'])->where('id', $userId)->get();

        $users = $users->map(function ($user) {

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'school_id' => $user->school_id,
                'address' => $user->address,
                'daily_calories' => $user->daily_calories,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'roles' => $user->roles->pluck('name'),
                'userallergy' => $user->user_allergies->map(function ($userAllergy) {
                    return [
                        'name' => $userAllergy->allergy->name,
                    ];
                })->pluck('name'),
            ];
        });

        return response()->json($users);

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

        $userId = Auth::id();

        $userallergy = UserAllergy::where('user_id', $userId);
        $userallergy->delete();

        if ($request->has('allergies')) {
            foreach ($request->allergies as $id) {
                UserAllergy::create([
                    'allergy_id' => $id,
                    'user_id' => $userId,
                ]);
            }
        }

        $birthday = new DateTime($request->birthday);
        $currentDate = new DateTime();
        $age = $currentDate->diff($birthday)->y;

        $daily_calories = $this->calculate_calories(
            $age,
            $request->gender,
            $request->weight,
            $request->height,
            $request->activitylevel
        );


        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function calculate_calories($age, $gender, $weight, $height, $activityLevel)
    {
        $userId = Auth::id();
        $User = User::find($userId);

        // Calculate BMR (Basal Metabolic Rate)
        $bmr = ($gender == 'Male')
            ? 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age)
            : 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);

        // Define activity level multipliers
        $activityMultipliers = [
            'sedentary' => 1.2,
            'lightly' => 1.375,
            'moderately' => 1.55,
            'very' => 1.725,
            'extra' => 1.9,
        ];

        $calories = $bmr * ($activityMultipliers[$activityLevel] ?? 1);
        $User->update(['daily_calories' => $calories,'calories_credits' => $calories]);
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

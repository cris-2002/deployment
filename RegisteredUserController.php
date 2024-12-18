<?php

namespace App\Http\Controllers\Auth;

use DateTime;
use App\Models\User;
use App\Models\Allergy;
use Illuminate\View\View;
use App\Models\UserAllergy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $allergies = Allergy::pluck('name', 'id')->all();
        return view('auth.register', ['allergies' => $allergies]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'school_id' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {

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

            $user = User::create([
                'name' => $request->name,
                'address' => $request->address,
                'daily_calories' => $daily_calories,
                'email' => $request->email,
                'birthday'=> $request->birthday,
                'gender' => $request->gender,
                'weight' => $request->weight,
                'height' => $request->height,
                'activitylevel' => $request->activitylevel,
                'school_id' => $request->school_id,
                'password' => Hash::make($request->password),
            ]);

            if ($request->filled('allergies')) {
                $userAllergies = collect($request->allergies)->map(function ($id) use ($user) {
                    return [
                        'allergy_id' => $id,
                        'user_id' => $user->id,
                    ];
                });

                UserAllergy::insert($userAllergies->toArray());
            }

            DB::table('model_has_roles')->insert([
                'role_id' => '4',
                'model_type' => 'App\Models\User',
                'model_id' => $user->id,
            ]);

            event(new Registered($user));
            Auth::login($user);

            return redirect()->route('shop.index');
        } catch (\Exception $e) {
            // Log the error (optional)
            // logger($e);

            return redirect()->back()->withErrors(['error' => 'Registration failed, please try again.']);
        }
    }

    /**
     * Calculate daily calorie needs.
     * weight kg unit
     * height cm unit
     */
    public function calculate_calories($age, $gender, $weight, $height, $activityLevel)
    {
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

        return $bmr * ($activityMultipliers[$activityLevel] ?? 1);
    }
}

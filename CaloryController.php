<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CaloryController extends Controller
{
    public function calculateCalories(Request $request)
    {

        // $request->validate([
        //     'gender'=>['required','string'],
        //     'weight'=>['required','integer'],
        //     'height'=>['required','integer'],
        //     'age'=>['required','integer'],
        //     'activityLevel'=>['required','string']
        // ]);

        $gender = $request->gender;
        $weight = $request->weight; //kg unit
        $height = $request->height; // cm unit
        $age = $request->age;
        $activityLevel = $request->activityLevel;

        // Calculate BMR (Basal Metabolic Rate)
        if ($gender == 'male') {
            $bmr = 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
        } else {
            $bmr = 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
        }

        // Calculate daily calorie needs based on activity level
        switch ($activityLevel) {
            case 'sedentary':
                $calories = $bmr * 1.2;
                break;
            case 'lightly':
                $calories = $bmr * 1.375;
                break;
            case 'moderately':
                $calories = $bmr * 1.55;
                break;
            case 'very ':
                $calories = $bmr * 1.725;
                break;
            case 'extra':
                $calories = $bmr * 1.9;
                break;
            default:
                $calories = $bmr;
                break;
        }

        return $calories;
    }
}

<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        // User::where('calories_date_modified', '<', date('Y-m-d'))->increment('calories_credits', 1);
        // User::where('calories_date_modified', '<', date('Y-m-d'))->increment('calories_credits', 1);



        if (env('ENABLE_CALORIES_DAILY_RESETER')) {
            User::where('calories_date_modified', '<', date('Y-m-d'))
            ->update([
                'calories_credits' => DB::raw('daily_calories'),
                'calories_date_modified' => date('Y-m-d')
            ]);
        }

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}

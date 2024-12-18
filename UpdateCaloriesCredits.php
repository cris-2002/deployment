<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class UpdateCaloriesCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-calories-credits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //

        // $updatedRows = User::query()->increment('calories_credits', 5);
        $updatedRows = User::where('calories_date_modified', '<', now())->increment('calories_credits', 1);
        $this->info("Successfully updated calories credits for {$updatedRows} users.");

    }
}

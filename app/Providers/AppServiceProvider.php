<?php

namespace App\Providers;

use App\Models\LetterReferral;
use App\Models\Task;
use App\Observers\LetterReferralObserver;
use App\Observers\TaskObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        LetterReferral::observe(LetterReferralObserver::class);
        Task::observe(TaskObserver::class);
    }
}

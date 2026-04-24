<?php

use App\Support\DueReminderDispatcher;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('reminders:dispatch', function (DueReminderDispatcher $dispatcher) {
    $result = $dispatcher->dispatch();

    $this->info(sprintf(
        'Reminders dispatched. tasks=%d, letters=%d',
        $result['tasks'],
        $result['letters']
    ));
})->purpose('Dispatch due-soon and overdue reminders for tasks and letters');

Schedule::command('reminders:dispatch')->dailyAt('08:00');

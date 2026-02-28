<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduler
|--------------------------------------------------------------------------
| Run: * * * * * cd /path-to-project/backend && php artisan schedule:run >> /dev/null 2>&1
| Use for: daily reports, cleanup, availability batch jobs.
*/
// Schedule::job(new \App\Jobs\ExampleJob)->daily();

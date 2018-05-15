<?php

use Fisdap\AppHealthChecks\HealthChecksController;
use Fisdap\AppHealthChecks\TestController;

// set appmon/status route
Route::get('appmon/status', HealthChecksController::class.'@status');


// test/debug route
Route::get('/appmon/test', TestController::class.'@test');

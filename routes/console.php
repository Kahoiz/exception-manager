<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('mq:process')->everyMinute()->withoutOverlapping();

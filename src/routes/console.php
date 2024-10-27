<?php 

use Illuminate\Support\Facades\Schedule;

Schedule::command('approtickets:clean-cart')->everyMinute();

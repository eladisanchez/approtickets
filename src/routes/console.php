<?php 

use ApproTickets\Console\Commands\CleanCartCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CleanCartCommand::class)->everyMinute();

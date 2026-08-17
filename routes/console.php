<?php

use Illuminate\Support\Facades\Schedule;

// Schedule the statement emails to run on the 1st and 16th of every month at 6:00 AM
Schedule::command("app:send-bi-monthly-statements")->twiceMonthly(1, 16, "06:00")->withoutOverlapping();

// Schedule overdue reminders to run daily at 6:00 AM
Schedule::command("app:send-overdue-reminders")->dailyAt("06:00")->withoutOverlapping();

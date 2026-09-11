<?php

use Illuminate\Support\Facades\Schedule;

/*
 * Una vez al día basta: lo único que hace es que una reserva que ya pasó se lea
 * como «terminada» en lugar de «confirmada».
 */
Schedule::command('aparcado:close-bookings')->dailyAt('04:10');

<?php

declare(strict_types=1);

return [
    'reminder_time' => env('REMINDERS_TIME', '08:00'),
    'reminder_enabled' => env('REMINDERS_ENABLED', true),
    'reminder_statuses' => [
        'reserved',
    ],
];

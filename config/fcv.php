<?php

return [
    'version' => '0.1.0',

    'default_roles' => [
        'guardia',
        'coordinacion',
    ],

    // Retención de bitácora en meses (24 = 2 años)
    'access_log_retention_months' => 24,

    'course_entry_tolerance_options' => [10, 20, 30, 40, 50, 60, 'none'],

    // Broadcasting (configurable por el host)
    'broadcast' => [
        'enabled' => false,
        'channel_prefix' => 'fcv',
    ],
];

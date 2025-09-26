<?php

return [
    'version' => '0.1.0',

    // Retención de bitácora en meses (24 = 2 años)
    'access_log_retention_months' => 24,

    // Presets de reglas por organización
    'organization_rule_presets' => [
        'horario_estricto' => [
            'entry' => [
                'allowed' => true,
                'by_schedule' => true,
                'tolerance_minutes' => 20,
            ],
            'exit' => [
                'allowed' => false,
                'by_schedule' => true,
            ],
        ],
        'horario_flexible' => [
            'entry' => [
                'allowed' => true,
            ],
            'exit' => [
                'allowed' => true,
            ],
        ],
        'acceso_total' => [
            'entry' => [
                'allowed' => true,
            ],
            'exit' => [
                'allowed' => true,
            ],
        ],
    ],

    // Broadcasting (configurable por el host)
    'broadcast' => [
        'enabled' => false,
        'channel_prefix' => 'fcv',
    ],
];

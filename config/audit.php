<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modelos auditables
    |--------------------------------------------------------------------------
    |
    | Define los modelos que se auditarán por defecto. Cada entrada debe ser el
    | FQCN del modelo. El trait `HasAuditLogs` permite sobrescribir esta
    | configuración a nivel de clase.
    |
    */
    'models' => [
        App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Campos excluidos
    |--------------------------------------------------------------------------
    |
    | Campos que no deben registrarse en la auditoría (por ejemplo, contraseñas
    | o tokens sensibles).
    |
    */
    'exclude' => [
        'password',
        'remember_token',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retención de registros
    |--------------------------------------------------------------------------
    |
    | Cantidad de días que se conservarán los logs. Úsalo para programar
    | limpiezas periódicas a través de tareas programadas.
    |
    */
    'retention_days' => env('AUDIT_RETENTION_DAYS', 365),
];

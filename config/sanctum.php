<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Expiración de Tokens
    |--------------------------------------------------------------------------
    |
    | Define la expiración de los tokens generados por Sanctum. Puedes ajustar
    | "SANCTUM_EXPIRATION" en el archivo .env para personalizarlo.
    */
    'expiration' => env('SANCTUM_EXPIRATION'),

    /*
    |--------------------------------------------------------------------------
    | Habilidades permitidas para usuarios finales
    |--------------------------------------------------------------------------
    |
    | Lista blanca de habilidades que los usuarios pueden asignar a sus propias
    | API keys mediante el modo de autogestión. Los administradores no están
    | limitados por estas entradas.
    */
    'user_abilities' => [
        'read',
        'write',
        'webhooks',
    ],
];

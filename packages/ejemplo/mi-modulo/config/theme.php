<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mi Módulo Theme
    |--------------------------------------------------------------------------
    |
    | Theme de ejemplo con colores verde y naranja.
    |
    */

    'name' => 'Mi Módulo Theme',

    'mode' => 'auto', // 'light', 'dark', o 'auto'

    'colors' => [
        'light' => [
            // Colores principales - Verde
            'primary' => '142.1 76.2% 36.3%',       // #10b981 - Verde esmeralda
            'primary-foreground' => '0 0% 100%',    // Blanco
            
            // Colores secundarios - Naranja
            'secondary' => '24.6 95% 53.1%',        // #fb923c - Naranja
            'secondary-foreground' => '0 0% 100%',  // Blanco
            
            // Accent - Verde más claro
            'accent' => '142.1 70.6% 45.3%',        // #22c55e - Verde claro
            'accent-foreground' => '0 0% 100%',     // Blanco
            
            // Backgrounds y superficies
            'background' => '0 0% 100%',            // Blanco
            'foreground' => '0 0% 3.9%',            // Negro suave
            
            'card' => '0 0% 100%',
            'card-foreground' => '0 0% 3.9%',
            
            'popover' => '0 0% 100%',
            'popover-foreground' => '0 0% 3.9%',
            
            // Muted - Grises neutros
            'muted' => '0 0% 96.1%',
            'muted-foreground' => '0 0% 45.1%',
            
            // Destructive - Rojo estándar
            'destructive' => '0 84.2% 60.2%',
            'destructive-foreground' => '0 0% 98%',
            
            // Bordes
            'border' => '0 0% 89.8%',
            'input' => '0 0% 89.8%',
            'ring' => '142.1 76.2% 36.3%',          // Verde para focus
        ],
        
        'dark' => [
            // Colores principales - Verde más suave
            'primary' => '142.1 70.6% 45.3%',       // Verde más claro
            'primary-foreground' => '0 0% 98%',
            
            // Colores secundarios - Naranja
            'secondary' => '24.6 95% 53.1%',
            'secondary-foreground' => '0 0% 98%',
            
            // Accent - Verde vibrante
            'accent' => '142.1 76.2% 36.3%',
            'accent-foreground' => '0 0% 98%',
            
            // Backgrounds oscuros
            'background' => '0 0% 3.9%',
            'foreground' => '0 0% 98%',
            
            'card' => '0 0% 3.9%',
            'card-foreground' => '0 0% 98%',
            
            'popover' => '0 0% 3.9%',
            'popover-foreground' => '0 0% 98%',
            
            // Muted oscuro
            'muted' => '0 0% 14.9%',
            'muted-foreground' => '0 0% 63.9%',
            
            // Destructive
            'destructive' => '0 62.8% 30.6%',
            'destructive-foreground' => '0 0% 98%',
            
            // Bordes oscuros
            'border' => '0 0% 14.9%',
            'input' => '0 0% 14.9%',
            'ring' => '142.1 70.6% 45.3%',
        ],
    ],

    'typography' => [
        'font-family' => 'Inter, system-ui, -apple-system, sans-serif',
    ],

    'spacing' => [
        'radius' => '0.5rem',
    ],
];

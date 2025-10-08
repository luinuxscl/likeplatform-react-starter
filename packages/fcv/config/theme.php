<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FCV Package Theme
    |--------------------------------------------------------------------------
    |
    | Theme corporativo de FCV con colores rojos característicos.
    |
    */

    'name' => 'FCV Theme',

    'mode' => 'auto', // 'light', 'dark', o 'auto'

    'colors' => [
        'light' => [
            // Colores principales - Rojo FCV
            'primary' => '0 84.2% 60.2%',           // #ef4444 - Rojo vibrante
            'primary-foreground' => '0 0% 100%',    // Blanco
            
            // Colores secundarios - Naranja complementario
            'secondary' => '24.6 95% 53.1%',        // #fb923c - Naranja
            'secondary-foreground' => '0 0% 100%',  // Blanco
            
            // Accent - Rojo más oscuro
            'accent' => '0 72.2% 50.6%',            // #dc2626 - Rojo oscuro
            'accent-foreground' => '0 0% 100%',     // Blanco
            
            // Backgrounds y superficies
            'background' => '0 0% 100%',            // Blanco
            'foreground' => '0 0% 3.9%',            // Negro suave
            
            'card' => '0 0% 100%',
            'card-foreground' => '0 0% 3.9%',
            
            'popover' => '0 0% 100%',
            'popover-foreground' => '0 0% 3.9%',
            
            // Muted - Grises cálidos
            'muted' => '0 0% 96.1%',                // Gris muy claro
            'muted-foreground' => '0 0% 45.1%',     // Gris medio
            
            // Destructive - Rojo intenso
            'destructive' => '0 84.2% 60.2%',
            'destructive-foreground' => '0 0% 98%',
            
            // Bordes
            'border' => '0 0% 89.8%',               // Gris claro
            'input' => '0 0% 89.8%',
            'ring' => '0 84.2% 60.2%',              // Rojo para focus
        ],
        
        'dark' => [
            // Colores principales - Rojo más suave para dark mode
            'primary' => '0 72.2% 50.6%',           // Rojo menos intenso
            'primary-foreground' => '0 0% 98%',
            
            // Colores secundarios
            'secondary' => '24.6 95% 53.1%',        // Naranja
            'secondary-foreground' => '0 0% 98%',
            
            // Accent
            'accent' => '0 84.2% 60.2%',            // Rojo vibrante
            'accent-foreground' => '0 0% 98%',
            
            // Backgrounds oscuros
            'background' => '0 0% 3.9%',            // Negro suave
            'foreground' => '0 0% 98%',             // Blanco suave
            
            'card' => '0 0% 3.9%',
            'card-foreground' => '0 0% 98%',
            
            'popover' => '0 0% 3.9%',
            'popover-foreground' => '0 0% 98%',
            
            // Muted oscuro
            'muted' => '0 0% 14.9%',                // Gris oscuro
            'muted-foreground' => '0 0% 63.9%',     // Gris claro
            
            // Destructive
            'destructive' => '0 62.8% 30.6%',
            'destructive-foreground' => '0 0% 98%',
            
            // Bordes oscuros
            'border' => '0 0% 14.9%',
            'input' => '0 0% 14.9%',
            'ring' => '0 72.2% 50.6%',
        ],
    ],

    'typography' => [
        'font-family' => 'Inter, system-ui, -apple-system, sans-serif',
    ],

    'spacing' => [
        'radius' => '0.5rem',
    ],
];

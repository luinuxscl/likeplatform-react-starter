<?php

return [
    'themes' => [
        'enabled' => env('EXPANSION_THEMES_ENABLED', true),
        'default_theme' => env('EXPANSION_DEFAULT_THEME', 'zinc'),
        'available_themes' => [
            'zinc' => [
                'name' => 'Zinc',
                'colors' => [
                    // Minimal set aligned with shadcn/ui CSS vars footprint
                    'background' => 'hsl(0 0% 100%)',
                    'foreground' => 'hsl(240 10% 3.9%)',
                    'card' => 'hsl(0 0% 100%)',
                    'card-foreground' => 'hsl(240 10% 3.9%)',
                    'popover' => 'hsl(0 0% 100%)',
                    'popover-foreground' => 'hsl(240 10% 3.9%)',
                    'primary' => 'hsl(240 5.9% 10%)',
                    'primary-foreground' => 'hsl(0 0% 98%)',
                    'secondary' => 'hsl(240 4.8% 95.9%)',
                    'secondary-foreground' => 'hsl(240 5.9% 10%)',
                    'muted' => 'hsl(240 4.8% 95.9%)',
                    'muted-foreground' => 'hsl(240 3.8% 46.1%)',
                    'accent' => 'hsl(240 4.8% 95.9%)',
                    'accent-foreground' => 'hsl(240 5.9% 10%)',
                    'destructive' => 'hsl(0 84.2% 60.2%)',
                    'destructive-foreground' => 'hsl(0 0% 98%)',
                    'border' => 'hsl(240 5.9% 90%)',
                    'input' => 'hsl(240 5.9% 90%)',
                    'ring' => 'hsl(240 5% 64.9%)',
                ],
            ],
            'slate' => [
                'name' => 'Slate',
                'colors' => [
                    'background' => 'hsl(0 0% 100%)',
                    'foreground' => 'hsl(222.2 47.4% 11.2%)',
                    'card' => 'hsl(0 0% 100%)',
                    'card-foreground' => 'hsl(222.2 47.4% 11.2%)',
                    'popover' => 'hsl(0 0% 100%)',
                    'popover-foreground' => 'hsl(222.2 47.4% 11.2%)',
                    'primary' => 'hsl(221.2 83.2% 53.3%)',
                    'primary-foreground' => 'hsl(210 40% 98%)',
                    'secondary' => 'hsl(210 40% 96.1%)',
                    'secondary-foreground' => 'hsl(222.2 47.4% 11.2%)',
                    'muted' => 'hsl(210 40% 96.1%)',
                    'muted-foreground' => 'hsl(215.4 16.3% 46.9%)',
                    'accent' => 'hsl(210 40% 96.1%)',
                    'accent-foreground' => 'hsl(222.2 47.4% 11.2%)',
                    'destructive' => 'hsl(0 84.2% 60.2%)',
                    'destructive-foreground' => 'hsl(210 40% 98%)',
                    'border' => 'hsl(214.3 31.8% 91.4%)',
                    'input' => 'hsl(214.3 31.8% 91.4%)',
                    'ring' => 'hsl(221.2 83.2% 53.3%)',
                ],
            ],
        ],
        'custom_themes' => [
            // Permite registrar temas personalizados desde config o provider
        ],
    ],
];

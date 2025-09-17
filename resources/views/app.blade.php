<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        {{-- SSR: Inject safe theme variables to avoid FOUC on first paint --}}
        @php
            $currentTheme = session('expansion.theme', config('expansion.themes.default_theme'));
            $themes = config('expansion.themes.available_themes', []);
            $colors = $themes[$currentTheme]['colors'] ?? [];
            $safeKeys = ['primary', 'primary-foreground', 'accent', 'accent-foreground', 'ring'];

            $toCss = function(array $map, array $keys): string {
                $buf = '';
                foreach ($keys as $k) {
                    if (isset($map[$k])) {
                        $buf .= "--{$k}: {$map[$k]};";
                    }
                }
                return $buf;
            };
            $safeCss = $toCss($colors, $safeKeys);
        @endphp
        <style>
            :root { {!! $safeCss !!} }
            html.dark { {!! $safeCss !!} }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

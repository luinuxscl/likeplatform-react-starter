<?php

namespace App\Console\Commands;

use App\Services\ThemeService;
use Illuminate\Console\Command;

/**
 * Comando para compilar themes de packages
 */
class ThemeCompileCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'theme:compile {--clear : Limpiar caché antes de compilar}';

    /**
     * The console command description.
     */
    protected $description = 'Compila los themes de todos los packages';

    /**
     * Execute the console command.
     */
    public function handle(ThemeService $themeService): int
    {
        if ($this->option('clear')) {
            $this->info('🧹 Limpiando caché de themes...');
            $themeService->clearCache();
        }

        $this->info('🎨 Compilando themes...');

        $themes = $themeService->getThemes(useCache: false);
        $css = $themeService->getCompiledCss(useCache: false);

        $this->newLine();
        $this->info('✅ Themes compilados exitosamente!');
        $this->newLine();

        // Mostrar tabla de themes
        $tableData = [];
        foreach ($themes as $packageName => $theme) {
            $tableData[] = [
                $packageName,
                $theme['name'] ?? $packageName,
                isset($theme['colors']['light']) ? '✓' : '✗',
                isset($theme['colors']['dark']) ? '✓' : '✗',
            ];
        }

        $this->table(
            ['Package', 'Theme Name', 'Light', 'Dark'],
            $tableData
        );

        $this->newLine();
        $this->line('CSS generado: '.strlen($css).' bytes');

        return self::SUCCESS;
    }
}

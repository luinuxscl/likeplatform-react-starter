<?php

namespace App\Console\Commands;

use App\Services\ThemeService;
use Illuminate\Console\Command;

/**
 * Comando para limpiar caché de themes
 */
class ThemeClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'theme:clear';

    /**
     * The console command description.
     */
    protected $description = 'Limpia la caché de themes compilados';

    /**
     * Execute the console command.
     */
    public function handle(ThemeService $themeService): int
    {
        $this->info('🧹 Limpiando caché de themes...');

        $themeService->clearCache();

        $this->info('✅ Caché de themes limpiada exitosamente!');

        return self::SUCCESS;
    }
}

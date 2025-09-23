<?php

namespace App\Console\Commands;

use App\Models\Option;
use App\Services\Options;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\AsCommand;

#[AsCommand(name: 'option:clear', description: 'Limpia la caché de opciones (una clave o todas)')]
class OptionClear extends Command
{
    protected $signature = 'option:clear {key? : Option key. If omitted, clears cache for all options}';
    protected $description = 'Limpia la caché de opciones (una clave o todas)';

    public function handle(Options $options): int
    {
        $key = $this->argument('key');
        if ($key) {
            $options->forget((string) $key);
            $this->info("Caché limpiada para la opción '{$key}'.");
            return self::SUCCESS;
        }

        // Limpiar todas
        foreach (Option::pluck('key') as $k) {
            $options->forget((string) $k);
        }
        $this->info('Caché de todas las opciones limpiada.');
        return self::SUCCESS;
    }
}

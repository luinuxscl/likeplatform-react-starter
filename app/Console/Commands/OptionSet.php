<?php

namespace App\Console\Commands;

use App\Services\Options;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\AsCommand;

#[AsCommand(name: 'option:set', description: 'Establece el valor de una opción y actualiza la caché')]
class OptionSet extends Command
{
    protected $signature = 'option:set {key : Option key} {value : Value} {--type=} {--group=} {--description=}';
    protected $description = 'Establece el valor de una opción y actualiza la caché';

    public function handle(Options $options): int
    {
        $key = (string) $this->argument('key');
        $value = $this->argument('value');
        $type = $this->option('type');
        $group = $this->option('group');
        $description = $this->option('description');

        // Si tipo es json, intentar decodificar de entrada
        if ($type === 'json') {
            $decoded = json_decode((string) $value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            } else {
                $this->error('El valor JSON es inválido.');
                return self::INVALID;
            }
        }

        $options->set($key, $value, $type, $group, $description);
        $this->info("Opción '{$key}' actualizada.");
        return self::SUCCESS;
    }
}

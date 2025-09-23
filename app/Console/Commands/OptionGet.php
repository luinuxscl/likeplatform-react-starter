<?php

namespace App\Console\Commands;

use App\Services\Options;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\AsCommand;

#[AsCommand(name: 'option:get', description: 'Obtiene el valor de una opción desde la base de datos (con caché)')]
class OptionGet extends Command
{
    protected $signature = 'option:get {key : Option key} {--default=}';
    protected $description = 'Obtiene el valor de una opción desde la base de datos (con caché)';

    public function handle(Options $options): int
    {
        $key = (string) $this->argument('key');
        $default = $this->option('default');
        $value = $options->get($key, $default);

        $this->line(is_scalar($value) || $value === null ? (string) $value : json_encode($value));
        return self::SUCCESS;
    }
}

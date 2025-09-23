<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\Options;

class OptionSeeder extends Seeder
{
    /**
     * Seed default application options.
     */
    public function run(): void
    {
        $options = app(Options::class);

        $options->set(
            'app.name',
            'LikePlatform',
            'string',
            'general',
            'Nombre visible de la aplicación'
        );

        $options->set(
            'app.description',
            'Plataforma moderna y flexible para construir experiencias digitales.',
            'text',
            'general',
            'Descripción corta de la aplicación'
        );

        $options->set(
            'app.date_format',
            'dd/mm/YYYY',
            'string',
            'i18n',
            'Formato de fecha por defecto'
        );

        $options->set(
            'app.timezone',
            'America/Santiago',
            'string',
            'i18n',
            'Zona horaria por defecto'
        );
    }
}

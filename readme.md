# Appro Tickets

## Instal·lació

### Afegir el paquet a composer.json:

    {
        "require": {
            "your-vendor/laravel-common-module": "dev-main"
        },
        "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/your-vendor/laravel-common-module"
            }
        ]
    }

### Executar

    composer update

### Registrar el provider

Afegir a config/app.php (Aquest pas segurament no és necessari en les últimes versions de Laravel)

    'providers' => [
        ApproticketsServiceProvider::class,
    ];

### Publica l'arxiu de configuració

    php artisan vendor:publish --provider="ApproticketsServiceProvider" --tag=config

I fer els canvis oportuns a l'arxiu tickets.php

### Models

- Usuaris: El model ha d'extendre ApproTickets\Models\User

### Filament

Publicar assets:

    php artisan filament:assets

### TODO

- Filament
- Indicar com s'han de dir les vistes
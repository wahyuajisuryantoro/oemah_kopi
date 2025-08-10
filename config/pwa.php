<?php

return [

    'install-button' => true,

    'manifest' => [
        'name' => 'Oemah Kopi',
        'short_name' => 'OMK',
        'background_color' => '#ffffff',
        'display' => 'fullscreen',
        'description' => 'Aplikasi Web Manajemen Cafe Oemah Kopi',
        'theme_color' => '#3367D6',
        'icons' => [
            [
                'src' => 'logo.png',
                'sizes' => '512x512',
                'type' => 'image/png',
            ],
        ],
    ],
    'debug' => env('APP_DEBUG', false),
    'livewire-app' => false,
];

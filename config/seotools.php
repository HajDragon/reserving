<?php
/**
 * @see https://github.com/artesaos/seotools
 */

return [
    'inertia' => env('SEO_TOOLS_INERTIA', false),
    'meta' => [
        'defaults'       => [
            'title'        => 'Experience Lab Reserveringssysteem',
            'titleBefore'  => false,
            'description'  => 'Reserveer snel en eenvoudig materialen en apparatuur van het Experience Lab van Summa College.',
            'separator'    => ' | ',
            'keywords'     => [
                'ervaringslab summa reserveren',
                'camera lenen summa',
                'apparatuur reserveren school',
                'microfoon lenen opleiding',
                'experience lab reservation',
            ],
            'canonical'    => 'current',
            'robots'       => 'all',
        ],
        'webmaster_tags' => [
            'google'    => null,
            'bing'      => null,
            'alexa'     => null,
            'pinterest' => null,
            'yandex'    => null,
            'norton'    => null,
        ],
        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        'defaults' => [
            'title'       => false,
            'description' => 'Reserveer snel en eenvoudig materialen en apparatuur van het Experience Lab van Summa College.',
            'url'         => null,
            'type'        => 'website',
            'site_name'   => 'Experience Lab Reserveringssysteem',
            'images'      => ['/apple-touch-icon.png'],
        ],
    ],
    'twitter' => [
        'defaults' => [
            'card' => 'summary',
            'image' => '/apple-touch-icon.png',
        ],
    ],
    'json-ld' => [
        'defaults' => [
            'title'       => false,
            'description' => 'Reserveer snel en eenvoudig materialen en apparatuur van het Experience Lab van Summa College.',
            'url'         => null,
            'type'        => 'WebPage',
            'images'      => [],
        ],
    ],
];

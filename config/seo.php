<?php

// Centrale SEO-instellingen (standaardwaarden voor meta tags op elke pagina).
// Pagina's kunnen deze overschrijven via props (:description op het layout-component).

return [
    'defaults' => [
        'description' => 'Reserveer snel en eenvoudig materialen en apparatuur van het Experience Lab van Summa College — camera\'s, microfoons, green screens en meer, voor studenten en medewerkers.',
        'keywords' => 'ervaringslab summa reserveren, camera lenen summa, apparatuur reserveren school, microfoon lenen opleiding, green screen lenen, audio apparatuur school',
        'robots' => 'index, follow',
        // ponytail: bestaande apple-touch-icon (166x166) als og:image; vervang door een echte 1200x630 banner als social sharing belangrijker wordt
        'og_image' => 'apple-touch-icon.png',
    ],
];

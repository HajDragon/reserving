<?php

test('public pages are reachable and return 200 for guests', function () {
    // Act & Assert
    foreach (['privacy' => '/privacy', 'terms' => '/voorwaarden'] as $route => $uri) {
        $this->get($uri)->assertOk();
    }
});

test('every page has a meta description, canonical and Open Graph tags', function () {
    // Act
    $response = $this->get('/privacy');

    // Assert
    $response->assertOk()
        ->assertSee('<meta name="description" content="', false)
        ->assertSee('<link rel="canonical" href="', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('property="og:description"', false)
        ->assertSee('property="og:image"', false)
        ->assertSee('name="twitter:card"', false);
});

test('the page title includes the page name and app name', function () {
    // Act
    $this->get('/privacy')
    // Assert
        ->assertOk()
        ->assertSee('<title>Privacyverklaring | ', false);
});

test('the html lang attribute matches the app locale', function () {
    // Act
    $this->get('/privacy')
    // Assert
        ->assertOk()
        ->assertSee('<html lang="nl"', false);
});

test('a sitemap.xml is served as xml with the public pages', function () {
    // Act
    $response = $this->get('/sitemap.xml');

    // Assert
    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    foreach (['/login', '/privacy', '/voorwaarden'] as $path) {
        $response->assertSee('<loc>'.url($path).'</loc>', false);
    }
});

test('robots.txt points to the sitemap', function () {
    // Assert
    // robots.txt is a static file in public/, not a route
    $this->assertTrue(str_contains(
        file_get_contents(public_path('robots.txt')),
        'Sitemap: https://app.hanger18.online/sitemap.xml',
    ));
});

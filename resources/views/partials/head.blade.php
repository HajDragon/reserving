@php
    $seoTitle = filled($title ?? null)
        ? $title.' - '.config('app.name', 'Laravel')
        : config('app.name', 'Laravel');
    $seoDescription = ($seoDescription ?? null) ?? config('seo.defaults.description');
    $seoKeywords = config('seo.defaults.keywords');
    $seoRobots = ($seoRobots ?? null) ?? config('seo.defaults.robots');
    $seoUrl = url()->current();
    $seoImage = asset(config('seo.defaults.og_image'));
@endphp
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="{{ $seoDescription }}" />
<meta name="keywords" content="{{ $seoKeywords }}" />
<meta name="robots" content="{{ $seoRobots }}" />
<link rel="canonical" href="{{ $seoUrl }}" />

<meta property="og:site_name" content="{{ config('app.name', 'Laravel') }}" />
<meta property="og:title" content="{{ $seoTitle }}" />
<meta property="og:description" content="{{ $seoDescription }}" />
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ $seoUrl }}" />
<meta property="og:image" content="{{ $seoImage }}" />

<meta name="twitter:card" content="summary" />
<meta name="twitter:title" content="{{ $seoTitle }}" />
<meta name="twitter:description" content="{{ $seoDescription }}" />
<meta name="twitter:image" content="{{ $seoImage }}" />

<title>{{ $seoTitle }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

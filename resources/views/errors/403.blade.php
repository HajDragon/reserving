<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — {{ __("Toegang geweigerd") }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    @fluxAppearance
</head>
<body class="flex min-h-screen items-center justify-center bg-white antialiased dark:bg-zinc-800">
    <div class="mx-auto max-w-md px-6 text-center">
        <div class="mb-6 text-7xl font-bold text-zinc-300 dark:text-zinc-600">403</div>
        <h1 class="mb-4 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __("Toegang geweigerd") }}</h1>
        <p class="mb-8 text-zinc-500 dark:text-zinc-400">
            {{ __("Je hebt geen toestemming om deze pagina te bekijken. Als je denkt dat dit een fout is, neem dan contact op met een beheerder.") }}
        </p>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-md bg-zinc-900 px-6 py-3 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300">
            {{ __("Terug naar dashboard") }}
        </a>
    </div>
</body>
</html>

@php
    $consented = cookie('cookie_consent', null);
@endphp

@if(!$consented)
<div
    x-data="{ show: true }"
    x-init="
        if (localStorage.getItem('cookie_consent') === 'accepted') {
            show = false;
        }
    "
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-0 left-0 right-0 z-50 border-t border-zinc-200 bg-white p-4 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
    role="alert"
    aria-live="polite"
>
    <div class="mx-auto flex max-w-7xl flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1">
            <p class="text-sm text-zinc-700 dark:text-zinc-300">
                {{ __("Wij gebruiken cookies voor het goed functioneren van deze website. Dit zijn functionele cookies die nodig zijn voor je login-sessie. Wij gebruiken geen tracking cookies.") }}
                <a href="{{ route('privacy') }}" class="underline hover:text-zinc-900 dark:hover:text-white">
                    {{ __('Lees meer in onze privacyverklaring') }}
                </a>.
            </p>
        </div>
        <div class="flex gap-2 shrink-0">
            <button
                @click="
                    localStorage.setItem('cookie_consent', 'accepted');
                    document.cookie = 'cookie_consent=accepted; path=/; max-age=31536000; SameSite=Lax';
                    show = false;
                "
                class="inline-flex items-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
                type="button"
            >
                {{ __('Accepteren') }}
            </button>
            <button
                @click="
                    localStorage.setItem('cookie_consent', 'rejected');
                    show = false;
                "
                class="inline-flex items-center rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                type="button"
            >
                {{ __('Weigeren') }}
            </button>
        </div>
    </div>
</div>
@endif

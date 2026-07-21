<footer class="px-6 md:px-16 lg:px-24 xl:px-32 pt-8 w-full text-black dark:text-white">
    <div class="flex flex-col md:flex-row justify-between w-full gap-10 border-b border-gray-500/30 pb-6">
        <div class="md:max-w-96">
            <x-app-logo class="size-12 mr-auto ml-12" />
            <p class="mt-6 text-sm">
                {{ config('app.name', 'Experience Lab Reserveringssysteem') }} — het reserveringssysteem
                van het Experience Lab van Summa College.
            </p>
        </div>
        <div class="flex-1 flex items-start md:justify-end gap-20">
            <nav aria-label="{{ __('Footer navigatie') }}">
                <h2 class="font-semibold mb-5 text-purple-800">{{ __('Links') }}</h2>
                <ul class="text-sm space-y-2">
                    <li><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('privacy') }}">{{ __('Privacyverklaring') }}</a></li>
                    <li><a href="{{ route('terms') }}">{{ __('Terms of Use') }}</a></li>
                </ul>
            </nav>
            <div>
                <h2 class="font-semibold mb-5 text-purple-800">{{ __('Contact') }}</h2>
                <div class="text-sm space-y-2">
                    <p>Experience Lab — Summa College</p>
                </div>
            </div>
        </div>
    </div>
    <p class="pt-4 text-center text-xs md:text-sm pb-5">
        &copy; {{ date('Y') }} {{ config('app.name', 'Summa ExperienceLab') }}. {{ __('Alle rechten voorbehouden.') }}
    </p>
</footer>

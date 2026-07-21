<x-layouts::app.sidebar :title="'Gegevensexport'">
    <div class="mx-auto max-w-4xl px-6 py-12 lg:px-8">
        <flux:heading class="mb-2" level="1">{{ __('Gegevensexport (AVG/GDPR)') }}</flux:heading>
        <flux:text class="mb-8 text-zinc-500">{{ __('Hier kun je al je persoonlijke gegevens bekijken en exporteren.') }}</flux:text>

        <div class="space-y-8">
            {{-- Personal Data --}}
            <flux:card>
                <flux:heading class="mb-4" level="2">{{ __('Persoonlijke gegevens') }}</flux:heading>
                <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach($userData['persoonlijke_gegevens'] as $key => $value)
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </flux:card>

            {{-- Reservations --}}
            <flux:card>
                <flux:heading class="mb-4" level="2">{{ __('Reserveringen (:count)', ['count' => count($userData['reserveringen'])]) }}</flux:heading>

                @if(count($userData['reserveringen']) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" aria-label="{{ __('Mijn reserveringen') }}">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Product') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Start') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Eind') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Status') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Hoeveelheid') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($userData['reserveringen'] as $reservation)
                                    <tr>
                                        <td class="px-3 py-2 text-zinc-900 dark:text-white">{{ $reservation['product'] }}</td>
                                        <td class="px-3 py-2 text-zinc-600 dark:text-zinc-400">{{ $reservation['start'] }}</td>
                                        <td class="px-3 py-2 text-zinc-600 dark:text-zinc-400">{{ $reservation['eind'] }}</td>
                                        <td class="px-3 py-2 text-zinc-600 dark:text-zinc-400">{{ $reservation['status'] }}</td>
                                        <td class="px-3 py-2 text-zinc-600 dark:text-zinc-400">{{ $reservation['hoeveelheid'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <flux:text class="text-zinc-500">{{ __('Je hebt nog geen reserveringen gemaakt.') }}</flux:text>
                @endif
            </flux:card>

            {{-- Export Button --}}
            <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div>
                    <flux:heading class="mb-1" level="3">{{ __('Gegevens exporteren') }}</flux:heading>
                    <flux:text class="text-sm text-zinc-500">{{ __('Download een JSON-bestand met al je gegevens. Dit is je recht op dataportabiliteit onder de AVG.') }}</flux:text>
                </div>
                <form method="POST" action="{{ route('gdpr.export.download') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        {{ __('Download als JSON') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app.sidebar>

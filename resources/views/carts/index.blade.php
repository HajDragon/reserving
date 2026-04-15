<x-layouts::app :title="__('My Cart')">
    <div class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('My Cart') }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Each authenticated user sees their own cart card here.') }}
            </p>
        </div>

        <flux:card class="overflow-hidden border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-6 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <flux:avatar :name="$user->name" :initials="$user->initials()" class="size-14" />

                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $user->name }}</h2>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="rounded-xl bg-zinc-100 px-4 py-2 text-sm font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    {{ __('Dedicated cart card') }}
                </div>
            </div>

            <div class="border-t border-zinc-200 px-6 py-5 dark:border-zinc-700">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('This card belongs only to the signed-in user and is ready for cart items, totals, or checkout actions.') }}
                </p>
            </div>
        </flux:card>
    </div>
</x-layouts::app>

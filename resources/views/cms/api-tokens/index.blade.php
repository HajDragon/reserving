<x-layouts::app :title="__('API Tokens')">
    <div class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('API Token Management') }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Admins can create personal access tokens for existing users and revoke them when they are no longer needed.') }}
            </p>
        </div>

        @if (session('generated_token'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-amber-950 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-100">
                <h2 class="text-lg font-semibold">{{ __('New token generated') }}</h2>
                <p class="mt-1 text-sm">
                    {{ __('Created for :name (:email) with token name :token.', [
                        'name' => session('generated_token_user_name'),
                        'email' => session('generated_token_user_email'),
                        'token' => session('generated_token_name'),
                    ]) }}
                </p>
                <div
                    class="mt-4 rounded-lg border border-amber-200 bg-white px-4 py-3 text-zinc-900 dark:border-amber-900/40 dark:bg-zinc-950 dark:text-zinc-100"
                    x-data="{
                        copied: false,
                        token: @js(session('generated_token')),
                        async copy() {
                            try {
                                await navigator.clipboard.writeText(this.token);
                                this.copied = true;
                                setTimeout(() => this.copied = false, 1500);
                            } catch (error) {
                                console.warn('Could not copy to clipboard');
                            }
                        }
                    }"
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <code class="block font-mono text-sm break-all">{{ session('generated_token') }}</code>
                        <button
                            type="button"
                            class="inline-flex h-9 items-center justify-center rounded-md border border-amber-300 bg-white px-3 text-sm font-medium text-amber-950 transition hover:bg-amber-100 dark:border-amber-800 dark:bg-zinc-900 dark:text-amber-100 dark:hover:bg-zinc-800"
                            @click="copy()"
                        >
                            {{ __('Copy token') }}
                        </button>
                    </div>
                    <p x-cloak x-show="copied" class="mt-2 text-xs text-emerald-700 dark:text-emerald-300">
                        {{ __('Copied to clipboard.') }}
                    </p>
                </div>
                <p class="mt-2 text-xs">
                    {{ __('Copy this token now. It will not be shown again after leaving this page.') }}
                </p>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Create Token') }}</h2>

                <form method="POST" action="{{ route('cms.api-tokens.store') }}" class="mt-4 space-y-4">
                    @csrf

                    <label class="block space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                        <span>{{ __('User') }}</span>
                        <select name="user_id" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            <option value="">{{ __('Select a user') }}</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                        <span>{{ __('Token Name') }}</span>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="{{ __('Integration Token') }}"
                            class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                        @error('name')
                            <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="space-y-2">
                        <div class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Abilities') }}</div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($ability_options as $abilityValue => $abilityLabel)
                                <label class="flex items-start gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                    <input
                                        type="checkbox"
                                        name="abilities[]"
                                        value="{{ $abilityValue }}"
                                        @checked(in_array($abilityValue, old('abilities', []), true))
                                        class="mt-0.5 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-700"
                                    >
                                    <span>{{ __($abilityLabel) }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('abilities')
                            <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                        @enderror
                        @error('abilities.*')
                            <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ __('The generated token will be returned once after creation and stored hashed in the database. Select the abilities this user should receive.') }}
                    </div>

                    <flux:button type="submit" variant="primary">{{ __('Create Token') }}</flux:button>
                </form>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Existing Tokens') }}</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $tokens->total() }} {{ __('total') }}</p>
                    </div>

                    <form method="GET" action="{{ route('cms.api-tokens.index') }}" class="grid gap-3 sm:min-w-[24rem]">
                        <label class="block space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                            <span>{{ __('Search') }}</span>
                            <input
                                type="text"
                                name="search"
                                value="{{ $filters['search'] }}"
                                placeholder="{{ __('Token name, user name, or email') }}"
                                class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                        </label>

                        <label class="block space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                            <span>{{ __('Ability') }}</span>
                            <select name="ability" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="">{{ __('All abilities') }}</option>
                                @foreach ($ability_options as $abilityValue => $abilityLabel)
                                    <option value="{{ $abilityValue }}" @selected($filters['ability'] === $abilityValue)>{{ __($abilityLabel) }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="flex items-center gap-3">
                            <flux:button type="submit" variant="primary">{{ __('Apply Filters') }}</flux:button>
                            <a href="{{ route('cms.api-tokens.index') }}" wire:navigate class="text-sm text-zinc-600 underline dark:text-zinc-300">{{ __('Clear') }}</a>
                        </div>
                    </form>
                </div>

                @if ($tokens->isEmpty())
                    <div class="mt-4 rounded-lg border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                        {{ __('No API tokens have been created yet.') }}
                    </div>
                @else
                    <div class="mt-4 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('User') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Token Name') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Abilities') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Last Used') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Created') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach ($tokens as $token)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                                <div class="font-medium">{{ $token->tokenable?->name ?? __('N/A') }}</div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $token->tokenable?->email ?? __('N/A') }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $token->name }}</td>
                                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                {{ implode(', ', \App\Enums\ApiTokenAbility::labelsFor($token->abilities ?? [])) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                {{ $token->last_used_at?->format('Y-m-d H:i') ?? __('Never') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                {{ $token->created_at?->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                <form method="POST" action="{{ route('cms.api-tokens.destroy', $token) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <flux:button
                                                        type="submit"
                                                        variant="danger"
                                                        size="sm"
                                                        onclick="return confirm('{{ __('Revoke this token? This action cannot be undone.') }}');"
                                                    >
                                                        {{ __('Revoke') }}
                                                    </flux:button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">
                            {{ $tokens->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>

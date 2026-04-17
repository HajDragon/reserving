<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col bg-white antialiased dark:bg-zinc-800">
        <!-- Desktop Header -->
        <flux:header sticky class="hidden xl:flex border-b border-zinc-200 bg-zinc-50 px-0 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:navbar class="-mb-px flex w-full items-center gap-0">
            <x-app-logo href="{{ route('dashboard') }}" wire:navigate class="mr-4 sm:hidden lg:block" />

                <flux:navbar.item icon="home" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                    {{ __('Products') }}
                </flux:navbar.item>

                <flux:navbar.item icon="shopping-cart" :href="route('carts.index')" :current="request()->routeIs('carts.*')" wire:navigate>
                    {{ __('Cart') }}
                </flux:navbar.item>

                <flux:navbar.item icon="calendar-days" :href="route('reservations.index')" :current="request()->routeIs('reservations.index')" wire:navigate>
                    {{ __('My Reservations') }}
                </flux:navbar.item>

                @can('access-reserving-dashboard')
                    <flux:separator vertical variant="subtle" class="my-2" />

                    <flux:navbar.item icon="rectangle-group" :href="route('reserving.index')" :current="request()->routeIs('reserving.*')" wire:navigate>
                        {{ __('Reserving Admin') }}
                    </flux:navbar.item>

                    <flux:navbar.item icon="clipboard-document-list" :href="route('cms.products.index')" :current="request()->routeIs('cms.products.*')" wire:navigate>
                        {{ __('Product CMS') }}
                    </flux:navbar.item>

                    <flux:navbar.item icon="archive-box" :href="route('cms.reservation-logs.index')" :current="request()->routeIs('cms.reservation-logs.*')" wire:navigate>
                        {{ __('Reservation Logs') }}
                    </flux:navbar.item>
                @endcan
            </flux:navbar>

            <flux:spacer />

            <x-desktop-user-menu class="hidden xl:block" :name="auth()->user()->name" />
        </flux:header>

        <!-- Mobile / Tablet Top Bar -->
        <flux:header class="xl:hidden border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="me-2" icon="bars-2" inset="left" />
            <x-app-logo href="{{ route('dashboard') }}" wire:navigate class="scale-90 lg:hidden sm:block" />
            <flux:spacer />
        </flux:header>

        <!-- Mobile Sidebar -->
        <flux:sidebar collapsible="mobile" sticky class="xl:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="xl:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Menu')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                        {{ __('Products') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="shopping-cart" :href="route('carts.index')" :current="request()->routeIs('carts.*')" wire:navigate>
                        {{ __('Carts') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="calendar-days" :href="route('reservations.index')" :current="request()->routeIs('reservations.index')" wire:navigate>
                        {{ __('My Reservations') }}
                    </flux:sidebar.item>

                    @can('access-reserving-dashboard')
                        <flux:sidebar.item icon="rectangle-group" :href="route('reserving.index')" :current="request()->routeIs('reserving.*')" wire:navigate>
                            {{ __('Reserving Admin') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="clipboard-document-list" :href="route('cms.products.index')" :current="request()->routeIs('cms.products.*')" wire:navigate>
                            {{ __('Product CMS') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="archive-box" :href="route('cms.reservation-logs.index')" :current="request()->routeIs('cms.reservation-logs.*')" wire:navigate>
                            {{ __('Reservation Logs') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <!-- Mobile User Menu -->
            <flux:sidebar.nav>
                <flux:dropdown position="top" align="end">
                    <flux:sidebar.profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar
                                        :name="auth()->user()->name"
                                        :initials="auth()->user()->initials()"
                                    />

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                {{ __('Settings') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-right-start-on-rectangle"
                                class="w-full cursor-pointer"
                                data-test="logout-button"
                            >
                                {{ __('Log out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </flux:sidebar.nav>
        </flux:sidebar>

        <main class="w-full flex-1">
            {{ $slot }}
        </main>

        <div class="w-full shrink-0">
            @include('layouts.app.footer')
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>

</html>

@props([
    'sidebar' => false,
])

<a {{ $attributes->merge(['href' => '/'])->class('flex flex-col items-center gap-2 text-center') }}>
    <div class="flex w-36 items-center justify-center ">
        <x-app-logo-icon class="h-auto w-full object-contain" />
    </div>
    {{-- <span class="text-sm font-semibold">Reserveering systeem</span> --}}
</a>

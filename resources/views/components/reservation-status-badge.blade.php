@props(['status'])

@php
    $styles = match ($status->value) {
        'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
        'reserved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'removal_request' => 'bg-red-100 text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-800',
        'still_waiting_for_return' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
        'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'returned' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "rounded-full px-2 py-1 text-xs font-medium uppercase $styles"]) }}>
    {{ $status->label() }}
</span>

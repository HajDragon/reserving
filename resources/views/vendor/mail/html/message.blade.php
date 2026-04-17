<x-mail::layout>
{{-- Header --}}
<x-slot:header>
    <x-mail::header :url="config('app.url')">
        <div style="text-align: center;">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/public/Logos/lab-logo.png'))) }}" alt="{{ config('app.name') }}" style="height: 60px; margin-bottom: 8px;" />
            <div style="font-weight: bold; font-size: 16px;">Reserving System</div>
        </div>
    </x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>

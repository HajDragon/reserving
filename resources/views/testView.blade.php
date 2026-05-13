@php

$Cities = ["Amsterdam", "Rotterdam", "Utrecht", "Eindhoven", "Groningen"];

@endphp

<x-layouts::app :title="__('test')">

    @php
        array_push($Cities, "New York");
    @endphp

<div class="bg-amber-600 glass p-4 box-shadow">
    @foreach ($Cities as $city)
        <p>{{ $city }}</p>
    @endforeach

</div>


</x-layouts::app>

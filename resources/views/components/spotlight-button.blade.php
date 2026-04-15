<button
    {{ $attributes->merge(['class' => 'btn-spotlight']) }}
    x-data="{ leftPos: '50%' }"
    @mousemove="leftPos = ($event.offsetX / $el.offsetWidth * 100) + '%'"
    @mouseleave="leftPos = '50%'"
>
    <span class="btn-spotlight-text">{{ $slot }}</span>
    <span class="btn-spotlight-circle" x-bind:style="{ left: leftPos }"></span>
</button>

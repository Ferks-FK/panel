<button
    x-ref="dropdown"
    x-on:click="open = !open"
    {{ $attributes->merge(['class' => 'flex items-center justify-center p-1 rounded hover:bg-slate-600']) }}
>
    {{ $slot }}
</button>

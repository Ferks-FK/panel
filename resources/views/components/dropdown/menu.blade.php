<div
    x-cloak
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="transform opacity-0 scale-95"
    x-transition:enter-end="transform opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="transform opacity-100 scale-100"
    x-transition:leave-end="transform opacity-0 scale-95"
    x-on:click.outside="open = false"
    x-anchor.offset.5.bottom-end="$refs.dropdown"
    class="min-w-[180px] border bg-slate-700 border-slate-400 p-2 rounded-md shadow-lg"
>
    {{ $slot }}
</div>

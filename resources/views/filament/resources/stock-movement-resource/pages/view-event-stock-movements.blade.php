<x-filament::page>
    <x-filament::card>
        <h2 class="text-xl font-bold mb-4">Items for Event: {{ $event->event_name }}</h2>
        {{ $this->table }}
    </x-filament::card>
</x-filament::page>

<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="consultarDNI">
            {{ $this->form }}
        </form>

        @include('filament.pages.DNI-consulte')
    </div>
</x-filament-panels::page>

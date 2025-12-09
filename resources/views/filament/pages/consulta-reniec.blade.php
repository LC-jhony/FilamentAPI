<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="consultarRUC">
            {{ $this->form }}
        </form>

        @include('filament.pages.RUC-consulte')
    </div>
</x-filament-panels::page>

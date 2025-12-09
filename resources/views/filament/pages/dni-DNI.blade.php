@php
    $livewire = $getLivewire();
    $resultado = $livewire->resultado ?? null;
    $data = $livewire->data ?? [];
@endphp

@if ($resultado)
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        style="margin-top: 1rem;">
        <div class="fi-section-content p-4">
            <pre class="text-sm text-gray-600 dark:text-gray-400"
                style="font-family: ui-monospace, monospace; white-space: pre-wrap; margin: 0;">{{ json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
@elseif ($data['dni'] ?? false)
    <p class="text-sm text-primary-600 dark:text-primary-400" style="margin-top: 1rem;">Buscando...</p>
@endif

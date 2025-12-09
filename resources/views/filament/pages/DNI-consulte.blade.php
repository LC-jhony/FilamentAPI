@if ($resultado)
    <div style="padding: 1rem; background-color: #f3f4f6; border-radius: 0.5rem; margin-top: 1rem;">
        <h3 style="font-weight: bold; margin-bottom: 0.5rem;">Resultado de la Consulta:</h3>
        <pre style="background-color: #ffffff; padding: 0.75rem; border-radius: 0.375rem; overflow: auto; max-height: 24rem;">{{ json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@elseif ($data['dni'] ?? false)
    <div style="padding: 1rem; background-color: #eff6ff; border-radius: 0.5rem; margin-top: 1rem;">
        <p style="color: #4b5563;">Consultando DNI {{ $data['dni'] }}...</p>
    </div>
@endif

<?php

namespace App\Trait;

use App\Services\ApiService;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;

trait TraitApiConsulta
{
    /**
     * Alias de campos devueltos por proveedores externos hacia los nombres usados en el formulario.
     *
     * Estructura:
     * - provider: string (por ejemplo, "reniec", "sunat")
     * - target: string (nombre del campo en el formulario)
     * - keys: string[] (posibles claves presentes en la respuesta de la API)
     *
     * Permite tolerar variaciones en los nombres de los atributos devueltos por cada API.
     * Ejemplo: para RENIEC, "first_name" puede venir como "first_name" o "nombres".
     */
    protected array $apiFieldAliases = [
        'reniec' => [
            'first_name' => ['first_name', 'nombres'],
            'first_last_name' => ['first_last_name', 'apellido_paterno'],
            'second_last_name' => ['second_last_name', 'apellido_materno'],
            'fecha_nacimiento' => ['fecha_nacimiento'],
            'genero' => ['genero'],
        ],
        'sunat' => [
            'razon_social' => ['razon_social'],
            'estado' => ['estado'],
            'direccion' => ['direccion'],
            'departamento' => ['departamento'],
            'provincia' => ['provincia'],
            'distrito' => ['distrito'],
            'ubigeo' => ['ubigeo'],
            'actividad_economica' => ['actividad_economica'],
            'fecha_inscripcion' => ['fecha_inscripcion'],
        ],
    ];

    /**
     * Configura alias de campos para un proveedor.
     *
     * - Si $merge es true, agrega/mergea los nuevos alias con los existentes sin perder compatibilidad.
     * - Si $merge es false, reemplaza completamente los alias del proveedor.
     *
     * @param string $provider Nombre del proveedor (por ejemplo, 'reniec' o 'sunat').
     * @param array $aliases Mapa de target => string|string[] con claves posibles en la respuesta.
     * @param bool $merge Si se deben mergear los alias con los existentes.
     * @return void
     */
    public function setFieldAliases(string $provider, array $aliases, bool $merge = true): void
    {
        if ($merge && isset($this->apiFieldAliases[$provider])) {
            foreach ($aliases as $target => $keys) {
                $existing = $this->apiFieldAliases[$provider][$target] ?? [];
                $this->apiFieldAliases[$provider][$target] = array_values(array_unique(array_merge($existing, (array) $keys)));
            }
        } else {
            $normalized = [];
            foreach ($aliases as $target => $keys) {
                $normalized[$target] = array_values(array_unique((array) $keys));
            }
            $this->apiFieldAliases[$provider] = $normalized;
        }
    }

    /**
     * Consulta datos de RENIEC a partir del DNI y setea los campos del formulario.
     *
     * - Valida el DNI (8 dígitos numéricos).
     * - Llama al servicio de API.
     * - En caso de error, limpia los campos relevantes y notifica.
     * - En caso de éxito, mapea y setea los valores usando alias configurados para 'reniec'.
     *
     * @param callable $set Callback de Filament para setear el valor de un campo: fn(string $field, mixed $value).
     * @return void
     */
    public function consultaRENIEC($set)
    {
        $dni = $this->form->getState()['dni'] ?? null;

        if (!$this->isValidAPI($dni, 'reniec')) {
            $set('first_name', null);
            $set('first_last_name', null);
            $set('second_last_name', null);
            return;
        }

        $service = $this->getApiService();
        $response = $service->consultarDNI($dni);

        if (!$response['success']) {
            $set('first_name', null);
            $set('first_last_name', null);
            $set('second_last_name', null);
            Notification::make()
                ->title($response['error'] ?: 'No se pudo consultar el DNI')
                ->warning()
                ->send();
            return;
        }

        $data = $response['data'];
        $this->setFromAliases($set, $data, $this->apiFieldAliases['reniec']);
    }

    /**
     * Consulta datos de SUNAT a partir del RUC y setea los campos del formulario.
     *
     * - Valida el RUC (11 dígitos numéricos).
     * - Llama al servicio de API.
     * - En caso de error, limpia campos visibles (por ejemplo, 'razon_social') y notifica.
     * - En caso de éxito, mapea y setea los valores usando alias configurados para 'sunat'.
     *
     * @param string $ruc Número de RUC a consultar.
     * @param callable $set Callback de Filament para setear el valor de un campo.
     * @return void
     */
    public function consultaRUC($ruc, $set)
    {
        if (!$this->isValidAPI($ruc, 'ruc')) {
            $set('razon_social', null);
            return;
        }

        $service = $this->getApiService();
        $response = $service->consultarRUC($ruc);

        if (!$response['success']) {
            $set('razon_social', null);
            Notification::make()
                ->title($response['error'] ?: 'No se pudo consultar el RUC')
                ->warning()
                ->send();
            return;
        }

        $data = $response['data'];
        $this->setFromAliases($set, $data, $this->apiFieldAliases['sunat']);
    }

    /**
     * Valida el valor según el tipo de documento soportado.
     *
     * - 'reniec': DNI debe tener exactamente 8 dígitos numéricos.
     * - 'ruc': RUC debe tener exactamente 11 dígitos numéricos.
     *
     * @param string|null $value Valor a validar.
     * @param string $type Tipo de documento ('reniec'|'ruc').
     * @return bool true si es válido, false en caso contrario.
     */
    protected function isValidAPI(?string $value, string $type): bool
    {
        return match ($type) {
            'reniec' => $value && strlen($value) === 8 && ctype_digit($value),
            'ruc' => $value && strlen($value) === 11 && ctype_digit($value),
            default => false,
        };
    }

    /**
     * Setea valores en el formulario a partir de alias de claves y datos devueltos por la API.
     *
     * - Recorre cada campo destino y busca el primer alias presente en $data.
     * - Si no encuentra valores, usa $defaults si está disponible.
     *
     * @param callable $set Callback para setear valores en el formulario.
     * @param array $data Datos devueltos por la API.
     * @param array $aliases Mapa de "target" => [alias1, alias2, ...].
     * @param array $defaults Valores por defecto por campo destino.
     * @return void
     */
    protected function setFromAliases(callable $set, array $data, array $aliases, array $defaults = []): void
    {
        foreach ($aliases as $target => $keys) {
            $value = null;
            foreach ($keys as $key) {
                if (array_key_exists($key, $data)) {
                    $value = $data[$key];
                    break;
                }
            }
            $set($target, $value ?? ($defaults[$target] ?? null));
        }
    }

    /**
     * Obtiene una instancia del servicio de API desde el contenedor.
     * Permite inyección y mocking en pruebas.
     *
     * @return ApiService Instancia lista para realizar consultas.
     */
    protected function getApiService(): ApiService
    {
        return app(ApiService::class);
    }
}

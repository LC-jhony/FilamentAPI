<?php

namespace App\Trait;

use App\Services\ApiService;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;

trait TraitApiConsulta
{
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
    protected function isValidAPI(?string $value, string $type): bool
    {
        return match ($type) {
            'reniec' => $value && strlen($value) === 8 && ctype_digit($value),
            'ruc' => $value && strlen($value) === 11 && ctype_digit($value),
            default => false,
        };
    }

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

    protected function getApiService(): ApiService
    {
        return app(ApiService::class);
    }
}

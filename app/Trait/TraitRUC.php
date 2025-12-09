<?php

namespace App\Trait;

use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Http;

trait TraitRUC
{
    public function consultarRUC(Set $set): void
    {
        $token = '5GQEkpGu69GJdUOn9eRvqqZbci68LAvQHBzLApNNLcsd9YofjWu6zI1A5zuW';

        $ruc = $this->data['ruc'] ?? null;

        if (!$ruc || strlen($ruc) !== 11) {
            $set('razon_social', null);
            $set('estado', null);
            $set('direccion', null);
            $set('departamento', null);
            $set('provincia', null);
            $set('distrito', null);
            $set('ubigeo', null);
            $set('actividad_economica', null);
            $set('fecha_inscripcion', null);
            return;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])->get("https://api.codart.cgrt.net/api/v1/consultas/sunat/ruc/{$ruc}");

            if ($response->successful()) {
                $this->resultado = $response->json();
                $data = $this->resultado['result'] ?? [];

                $set('razon_social', $data['razon_social'] ?? null);
                $set('estado', $data['estado'] ?? null);
                $set('direccion', $data['direccion'] ?? null);
                $set('departamento', $data['departamento'] ?? null);
                $set('provincia', $data['provincia'] ?? null);
                $set('distrito', $data['distrito'] ?? null);
                $set('ubigeo', $data['ubigeo'] ?? null);
                $set('actividad_economica', $data['actividad_economica'] ?? null);
                $set('fecha_inscripcion', $data['fecha_inscripcion'] ?? null);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al consultar con la API: ' . $e->getMessage());
        }
    }
}

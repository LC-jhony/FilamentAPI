<?php

namespace App\Trait;

use Illuminate\Support\Facades\Http;


trait TraitDNI
{
    public function consultarDNI()
    {
        $token = '5GQEkpGu69GJdUOn9eRvqqZbci68LAvQHBzLApNNLcsd9YofjWu6zI1A5zuW';
        try {
            $dni = $this->data['dni'] ?? null;
            if (!$dni) {
                session()->flash('error', 'DNI es requerido');
                return;
            }
            $response = Http::withHeaders([
                'content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])->get("https://api.codart.cgrt.net/api/v1/consultas/reniec/dni/{$dni}");
            $this->resultado = $response->json();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al consultar con la API: ' . $e->getMessage());
        }
    }
}

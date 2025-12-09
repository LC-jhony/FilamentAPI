<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Illuminate\Support\Facades\Http;


class ConsultaDNI extends Page
{
    // https://api.codart.cgrt.net/
    // https://api.codart.cgrt.net/documentacion
    private $token = '5GQEkpGu69GJdUOn9eRvqqZbci68LAvQHBzLApNNLcsd9YofjWu6zI1A5zuW';

    protected string $view = 'filament.pages.consulta-d-n-i';
    public $data = [];
    public $resultado = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Consulta dni')
                    ->schema([
                        TextInput::make('dni')
                            ->required()
                            ->live()
                            ->debounce(500)
                            ->afterStateUpdated(fn() => $this->consultarDNI()),
                    ])
            ])
            ->statePath('data');
    }
    public function consultarDNI()
    {
        try {
            $dni = $this->data['dni'] ?? null;

            if (!$dni) {
                session()->flash('error', 'DNI es requerido');
                return;
            }

            $response = Http::withHeaders([
                'content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ])->get("https://api.codart.cgrt.net/api/v1/consultas/reniec/dni/{$dni}");
            $this->resultado = $response->json();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al consultar con la API: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\View;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Support\Facades\Http;

class ConsultaReniec extends Page implements HasSchemas
{
    private $token = '5GQEkpGu69GJdUOn9eRvqqZbci68LAvQHBzLApNNLcsd9YofjWu6zI1A5zuW';
    protected string $view = 'filament.pages.consulta-reniec';
    public $data = [];
    public $resultado = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Consulta RUC')
                    ->schema([
                        TextInput::make('ruc')
                            ->required()
                            ->live()
                            ->debounce(500)
                            ->afterStateUpdated(fn() => $this->consultarRUC()),
                    ])
            ])
            ->statePath('data');
    }

    public function consultarRUC()
    {
        try {
            $ruc = $this->data['ruc'] ?? null;

            if (!$ruc) {
                session()->flash('error', 'RUC es requerido');
                return;
            }

            $response = Http::withHeaders([
                'content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ])->get("https://api.codart.cgrt.net/api/v1/consultas/sunat/ruc/{$ruc}");

            $this->resultado = $response->json();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al consultar con la API: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Filament\Pages;

use App\Trait\TraitApiConsulta;
use Dom\Text;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\ToggleButtons;

class ApiConsulta extends Page
{
    use TraitApiConsulta;
    protected string $view = 'filament.pages.api-consulta';
    public $data = [];
    public $resultado = null;
    public function mount(): void
    {
        $this->form->fill();
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Consulta RENIEC')
                    ->schema([
                        TextInput::make('dni')
                            ->numeric()
                            ->length(8)
                            ->live()
                            ->debounce(500)
                            ->afterStateUpdated(function ($state, $set) {
                                $this->consultaRENIEC($set);
                            }),
                        TextInput::make('first_name')
                            ->label('Nombres')
                            ->disabled(),
                        TextInput::make('first_last_name')
                            ->label('Nombres')
                            ->disabled(),
                        TextInput::make('second_last_name')
                            ->label('Nombres')
                            ->disabled(),
                    ]),
                Section::make('Consulta RUC')
                    ->schema([
                        TextInput::make('ruc')
                            ->numeric()
                            ->length(11)
                            ->live()
                            ->debounce(500)
                            ->afterStateUpdated(function ($state, $set) {
                                $this->consultaRUC($state, $set);
                            }),

                        TextInput::make('razon_social')
                            ->label('Razón Social')
                            ->disabled(),
                    ]),

            ])
            ->statePath('data');
    }
}

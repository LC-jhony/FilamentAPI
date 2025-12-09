<?php

namespace App\Filament\Pages;

use UnitEnum;
use BackedEnum;
use App\Trait\TraitRUC;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;

class RUc extends Page
{
    use TraitRUC;

    protected string $view = 'filament.pages.r-uc';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'RUC';
    protected static string|UnitEnum|null $navigationGroup = 'Documentos';

    public ?array $data = [];
    public $resultado = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Consulta RUC')
                    ->schema([
                        TextInput::make('ruc')
                            ->label('RUC')
                            ->required()
                            ->numeric()
                            ->length(11)
                            ->live()
                            ->debounce(500)
                            ->afterStateUpdated(function ($state, $set) {
                                $this->consultarRUC($set);
                            }),

                        TextInput::make('razon_social')
                            ->label('Razón Social')
                            ->readOnly(),
                        TextInput::make('estado')
                            ->label('Estado')
                            ->readOnly(),
                        TextInput::make('direccion')
                            ->label('Dirección')
                            ->readOnly(),
                        TextInput::make('departamento')
                            ->label('Departamento')
                            ->readOnly(),
                        TextInput::make('provincia')
                            ->label('Provincia')
                            ->readOnly(),
                        TextInput::make('distrito')
                            ->label('Distrito')
                            ->readOnly(),
                        TextInput::make('ubigeo')
                            ->label('Ubigeo')
                            ->readOnly(),
                        TextInput::make('actividad_economica')
                            ->label('Actividad Económica')
                            ->readOnly(),
                        TextInput::make('fecha_inscripcion')
                            ->label('Fecha de Inscripción')
                            ->readOnly(),
                    ])
            ])
            ->statePath('data');
    }
}

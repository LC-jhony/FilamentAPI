<?php

namespace App\Filament\Pages;

use UnitEnum;
use BackedEnum;
use App\Trait\TraitDNI;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\View;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;

class DNI extends Page
{
    use TraitDNI;
    protected string $view = 'filament.pages.d-n-i';
    public $data = [];
    public $resultado = null;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'DNI';

    protected static string|UnitEnum|null $navigationGroup = 'DNI';

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
                        View::make('filament.pages.dni-DNI', [
                            'resultado' => $this->resultado,
                            'data' => $this->data,
                        ]),
                    ])
            ])
            ->statePath('data');
    }

    // protected function getViewData(): array
    // {
    //     return array_merge(parent::getViewData(), [
    //         'resultado' => $this->resultado,
    //         'data' => $this->data,
    //     ]);
    // }
}

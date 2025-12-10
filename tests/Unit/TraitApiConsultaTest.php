<?php

namespace Tests\Unit;

use App\Services\ApiService;
use App\Filament\Pages\ApiConsulta;
use Illuminate\Support\Facades\App;
use Mockery;
use Tests\TestCase;

class TraitApiConsultaTest extends TestCase
{
    public function test_consulta_reniec_setea_campos_nombres_y_apellidos(): void
    {
        $mock = Mockery::mock(ApiService::class);
        $mock->shouldReceive('consultarDNI')->andReturn([
            'success' => true,
            'data' => [
                'first_name' => 'Juan',
                'first_last_name' => 'Pérez',
                'second_last_name' => 'García',
            ],
        ]);
        App::instance(ApiService::class, $mock);

        $page = app(ApiConsulta::class);

        $setCalls = [];
        $set = function ($key, $value) use (&$setCalls) {
            $setCalls[$key] = $value;
        };

        $page->form = new class {
            public function getState()
            {
                return ['dni' => '12345678'];
            }
        };

        $page->consultaRENIEC($set);

        $this->assertSame('Juan', $setCalls['first_name'] ?? null);
        $this->assertSame('Pérez', $setCalls['first_last_name'] ?? null);
        $this->assertSame('García', $setCalls['second_last_name'] ?? null);
    }

    public function test_alias_dinamicos_reniec_con_merge(): void
    {
        $mock = Mockery::mock(ApiService::class);
        $mock->shouldReceive('consultarDNI')->andReturn([
            'success' => true,
            'data' => [
                'nombre_completo' => 'Ana',
                'ape_pat' => 'Lopez',
                'ape_mat' => 'Soto',
            ],
        ]);
        App::instance(ApiService::class, $mock);

        $page = app(ApiConsulta::class);
        // agregar alias dinámicos que se sumen a los existentes
        $page->setFieldAliases('reniec', [
            'first_name' => ['nombre_completo'],
            'first_last_name' => ['ape_pat'],
            'second_last_name' => ['ape_mat'],
        ], merge: true);

        $setCalls = [];
        $set = function ($key, $value) use (&$setCalls) {
            $setCalls[$key] = $value;
        };

        $page->form = new class {
            public function getState()
            {
                return ['dni' => '12345678'];
            }
        };

        $page->consultaRENIEC($set);

        $this->assertSame('Ana', $setCalls['first_name'] ?? null);
        $this->assertSame('Lopez', $setCalls['first_last_name'] ?? null);
        $this->assertSame('Soto', $setCalls['second_last_name'] ?? null);
    }

    public function test_consulta_ruc_setea_razon_social(): void
    {
        $mock = Mockery::mock(ApiService::class);
        $mock->shouldReceive('consultarRUC')->andReturn([
            'success' => true,
            'data' => [
                'razon_social' => 'Mi Empresa SAC',
            ],
        ]);
        App::instance(ApiService::class, $mock);

        $page = app(ApiConsulta::class);

        $setCalls = [];
        $set = function ($key, $value) use (&$setCalls) {
            $setCalls[$key] = $value;
        };

        $page->consultaRUC('20123456789', $set);

        $this->assertSame('Mi Empresa SAC', $setCalls['razon_social'] ?? null);
    }
}

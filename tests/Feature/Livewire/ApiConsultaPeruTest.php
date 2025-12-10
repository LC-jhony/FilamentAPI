<?php

use App\Livewire\ApiConsultaPeru;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ApiConsultaPeru::class)
        ->assertStatus(200);
});

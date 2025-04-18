<?php

namespace Tests\Feature;

use ApproTickets\Filament\Resources\BannerResource;
use ApproTickets\Models\User;
use function Pest\Laravel\actingAs;
use ApproTickets\Models\Extract;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $user = User::where('name', 'admin')->first();
    actingAs($user);
});

it('can render page', function () {
    $this->get(BannerResource::getUrl('index'))->assertSuccessful();
})->skip('wip');

it('can download extracts', function () {
    $extract = Extract::find(1);

    livewire(\ApproTickets\Filament\Resources\ExtractResource\Pages\ListExtracts::class, [
        'extract' => $extract
    ])
        ->mountPageAction('downloadExcel');
})->skip('wip');

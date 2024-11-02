<?php

use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Filament::setCurrentPanel(
        Filament::getPanel('customer'),
    );
});

it('does not allow guests to access the customer panel', function () {
    $this->get('/customer')
        ->assertRedirect(route('filament.customer.auth.login'));
});

it('does not allow users with unverified emails to access the customer panel', function () {
    actingAs(User::factory()->unverified()->create())
        ->get('/customer')
        ->assertRedirect(route('filament.customer.auth.email-verification.prompt'));
});

it('allows logged in users with a verified email to access the panel', function () {
    actingAs(User::factory()->create())
        ->get('/customer')
        ->assertSuccessful();
});

<?php

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Auth\Register;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    Filament::setCurrentPanel(
        Filament::getPanel('customer'),
    );
});

it('does not allow authenticated users access to the register page', function () {
    actingAs(User::factory()->create())
        ->get(route('filament.customer.auth.register'))
        ->assertRedirect(route('filament.customer.pages.dashboard'));
});

it('it can register a user', function () {
    livewire(Register::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'test@email.com',
            'password' => 'filament12345',
            'passwordConfirmation' => 'filament12345',
        ])
        ->call('register')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'test@email.com')->firstOrFail();

    assertEquals([
        'name' => 'Test User',
        'email' => 'test@email.com',
    ], $user->only('name', 'email'));

    assertTrue(password_verify('filament12345', $user->password));

    assertEquals(auth()->id(), $user->id);
});

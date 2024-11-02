<?php

use App\Models\User;
use Filament\Pages\Auth\Login;
use Illuminate\Support\Facades\Hash;

use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertEquals;

it('can login with valid credentials', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'password' => Hash::make('password'),
    ]);

    livewire(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    assertEquals($user->id, auth()->id());
});

it('does not login with invalid credentials', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'password' => Hash::make('password'),
    ]);

    livewire(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password-wrong',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    assertEquals(null, auth()->id());
});

it('does not login a user with an unverified email', function () {
    $user = User::factory()->unverified()->create([
        'is_admin' => true,
        'password' => Hash::make('password'),
    ]);

    livewire(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    assertEquals(null, auth()->id());
});

it('does not login a user with is_admin set to false', function () {
    $user = User::factory()->create([
        'is_admin' => false,
        'password' => Hash::make('password'),
    ]);

    livewire(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    assertEquals(null, auth()->id());
});

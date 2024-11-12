<?php

use App\Filament\Customer\Resources\PostResource\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(
        Filament::getPanel('customer'),
    );

    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('can fill the repeater', function () {
    Repeater::fake();

    $post = Post::factory()->create([
        'tags' => ['tag1', 'tag2'],
    ]);

    livewire(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->assertFormSet([
            'tags' => [
                ['tag' => 'tag1'],
                ['tag' => 'tag2'],
            ],
        ]);
});

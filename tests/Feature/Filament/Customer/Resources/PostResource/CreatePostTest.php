<?php

use App\Filament\Customer\Resources\PostResource;
use App\Filament\Customer\Resources\PostResource\Pages\CreatePost;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Toggle;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertEquals;

beforeEach(function () {
    Filament::setCurrentPanel(
        Filament::getPanel('customer'),
    );

    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('can render the page', function (): void {
    get(PostResource::getUrl('create'))->assertSuccessful();
});

it('can create a post', function (): void {

    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'My first post',
            'slug' => 'my-first-post',
            'content' => 'This is my first post content',
            'published_at' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('posts', [
        'title' => 'My first post',
        'slug' => 'my-first-post',
        'content' => 'This is my first post content',
        'published_at' => now()->format('Y-m-d H:i:s'),
        'user_id' => $this->user->id,
    ]);
});

it('validates the required fields', function () {
    livewire(CreatePost::class)
        ->fillForm([])
        ->call('create')
        ->assertHasFormErrors([
            'title',
            'slug',
            'content',
        ]);
});

it('can automatically creates a slug from the title', function () {
    $title = 'My first post';

    livewire(CreatePost::class)
        ->fillForm([
            'title' => $title,
        ])
        ->assertFormSet(function (array $state) use ($title) {
            assertEquals($state['slug'], Str::slug($title));
        });
});

it('lets you edit the slug if your a admin', function () {
    livewire(CreatePost::class)
        ->assertFormFieldIsDisabled('slug');

    $this->user->update(['is_admin' => true]);

    livewire(CreatePost::class)
        ->assertFormFieldIsEnabled('slug');
});

it('has the right label for the published_at', function () {
    livewire(CreatePost::class)
        ->assertFormFieldExists('published_at', function (Toggle $field): bool {
            return $field->getLabel() === 'Publish';
        });
});

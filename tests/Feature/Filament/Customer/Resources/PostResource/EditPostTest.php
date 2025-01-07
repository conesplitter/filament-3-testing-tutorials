<?php

use App\Filament\Customer\Resources\PostResource;
use App\Filament\Customer\Resources\PostResource\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertStringStartsWith;

beforeEach(function () {
    Filament::setCurrentPanel(
        Filament::getPanel('customer'),
    );

    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('can render the page', function (): void {
    $post = Post::factory()->create();

    get(PostResource::getUrl('edit', [
        'record' => $post->getRouteKey(),
    ]))->assertSuccessful();

    livewire(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])->assertSuccessful();
});

it('fills the data into the form', function (): void {
    $post = Post::factory()->create();

    livewire(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->assertFormSet([
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
        ]);
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

it('fills the image into the component', function (): void {
    Storage::fake('public');

    $post = Post::factory()->create(['thumbnail' => 'post-thumbnails/image.jpg']);

    $uploadedFile = UploadedFile::fake()->image('image.jpg');
    Storage::disk('public')->putFileAs('post-thumbnails', $uploadedFile, 'image.jpg');

    livewire(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->assertFormSet(function (array $data) {
            $imageFirstKey = array_key_first($data['thumbnail']);
            assertEquals($data['thumbnail'][$imageFirstKey], 'post-thumbnails/image.jpg');
        });
});

it('can update the post', function (): void {
    Repeater::fake();

    $post = Post::factory()->create();

    livewire(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->fillForm([
            'title' => 'Updated Title',
            'content' => 'Updated Content',
            'tags' => [
                ['tag' => 'tag1'],
                ['tag' => 'tag2'],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $post->refresh();

    assertEquals([
        'title' => 'Updated Title',
        'content' => 'Updated Content',
        'tags' => ['tag1', 'tag2'],
    ], $post->only(['title', 'content', 'tags']));
});

test("you can't update the slug", function (): void {
    $post = Post::factory()->create(['slug' => 'slug']);

    livewire(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->fillForm([
            'slug' => 'updated-slug',
        ])
        ->call('save');

    assertNotEquals('updated-slug', $post->fresh()->slug);
});

it('can change the thumbnail', function (): void {
    Storage::fake('public');

    $post = Post::factory()->create(['thumbnail' => 'post-thumbnails/image.jpg']);

    $uploadedFile = UploadedFile::fake()->image('image.jpg');
    Storage::disk('public')->putFileAs('post-thumbnails', $uploadedFile, 'image.jpg');

    livewire(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->fillForm(['thumbnail' => null])
        ->call('save')
        ->fillForm([
            'title' => 'title',
            'slug' => 'slug',
            'content' => 'content',
            'thumbnail' => UploadedFile::fake()->image('new-thumbnail.jpg'),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $post->refresh();

    assertNotEquals('post-thumbnails/image.jpg', $post->thumbnail);
    assertStringStartsWith('post-thumbnails/', $post->thumbnail);

    Storage::disk('public')->assertExists($post->thumbnail);
});

it('can delete the post', function (): void {

    $post = Post::factory()->create();

    livewire(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    assertModelMissing($post);
});

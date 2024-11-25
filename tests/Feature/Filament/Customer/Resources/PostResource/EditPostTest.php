<?php

use App\Filament\Customer\Resources\PostResource\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
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

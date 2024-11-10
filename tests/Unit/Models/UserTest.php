<?php

use App\Models\Post;
use App\Models\User;

it('has many posts', function () {
    $user = User::factory()->hasPosts(3)->create();

    expect($user->posts)->toHaveCount(3)
        ->each->toBeInstanceOf(Post::class);
});

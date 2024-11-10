<?php

namespace App\Filament\Customer\Resources\PostResource\Pages;

use App\Filament\Customer\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['published_at'] = $data['published_at'] ? now()->format('Y-m-d H:i:s') : null;
        $data['user_id'] = auth()->id();

        return $data;
    }
}

<?php

namespace App\Filament\Support\Resources\UserResource\Pages;

use App\Filament\Support\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}

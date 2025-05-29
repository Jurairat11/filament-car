<?php

namespace App\Filament\Resources\HazardSourceResource\Pages;

use App\Filament\Resources\HazardSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHazardSource extends CreateRecord
{
    protected static string $resource = HazardSourceResource::class;
    protected static ?string $title = 'Create Hazard Source';
}


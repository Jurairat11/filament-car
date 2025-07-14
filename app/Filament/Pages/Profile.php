<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class Profile extends EditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        FileUpload::make('avatar')
                            ->label('Picture profile')
                            ->image()
                            ->imageEditor()
                            ->circleCropper()
                            ->directory('avatars')
                            ->moveFiles()
                            ->columnSpanFull()
                            ->helperText('The maximum picture size is 5MB')
                            //->acceptedFileTypes(['jpg'])
                            ->maxSize(5120) // 5MB
                            ->afterStateUpdated(function ($state) {
                                if (! $state instanceof TemporaryUploadedFile) return;

                                // Delete old avatar if exists
                                $oldAvatar = Auth::user()->avatar;
                                if ($oldAvatar && Storage::exists($oldAvatar)) {
                                    Storage::delete($oldAvatar);
                                }
                            }),
                            TextInput::make('emp_name')
                                ->label('First name')
                                ->required(),
                            TextInput::make('last_name')
                                ->label('Last name')
                                ->required(),
                            TextInput::make('emp_id')
                                ->label('Employee ID')
                                ->required(),
                            Select::make('dept_id')
                                ->label('Department')
                                ->searchable()
                                ->preload()
                                ->relationship('deptID','dept_name')
                                ->disabled()
                                ->required(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->columns(1),
            ]);
    }

}

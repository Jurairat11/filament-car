<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                ->schema ([
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
                ->placeholder('Select Department')
                ->relationship('deptID','dept_name')
                ->required(),
                TextInput::make('email')
                ->label('Email address')
                ->email()
                ->autocomplete(false),
                TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->required(fn(Page $livewire)=>($livewire instanceof CreateUser)),
                Select::make('roles')
                ->placeholder('Select Role')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload()
                ->options(function () {
                // ดึง role ทั้งหมด
                $roles = Role::pluck('name', 'id');

                // ถ้า user ปัจจุบันเป็น Safety, ไม่ให้แสดง 'Admin'
                if (Filament::auth()->user()?->hasRole('Safety')) {
                    $roles = $roles->filter(fn ($name) => $name !== 'Admin');
                }

                return $roles;
            }),
                Select::make('permissions')
                ->multiple()
                ->placeholder('Select Permissions')
                ->relationship('permissions','name')
                ->preload()

                ])->columns(2)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#'),
                TextColumn::make('emp_id')
                ->label('Employee ID')
                ->searchable(),
                TextColumn::make('emp_name')->label('First Name'),
                TextColumn::make('last_name')->label('Last Name'),
                TextColumn::make('email'),
                TextColumn::make('roles.name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                    'Admin' => 'gray',
                    'Safety' => 'success',
                    'User' => 'info',
                })
                    ->label('Role'),
            ])
            ->filters([
                SelectFilter::make('roles.name')
                ->label('Filter by Role')
                ->relationship('roles', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }


}

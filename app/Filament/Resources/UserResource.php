<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Filament Shield';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\Section::make('User Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required()
                            ->dehydrated(fn ($state) => !empty($state)),
                            
                        //Assign role to user but from the list of roles in the database
                        Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->required() 
                            
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                    //Role
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->roles->pluck('name')->implode(', ');
                    }),
            ])
            ->filters([
                //
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

    //All restrictions
    public static function shouldRegisterNavigation(): bool
{
    $user = Auth::user();

    return $user instanceof User && $user->hasRole('admin');
}

public static function canViewAny(): bool
{
    $user = Auth::user();

    return $user instanceof User && $user->hasRole('admin');
}

public static function canCreate(): bool
{
    $user = Auth::user();

    return $user instanceof User && $user->hasRole('admin');
}

public static function canEdit(Model $record): bool
{
   $user = Auth::user();

    return $user instanceof User && $user->hasRole('admin');
}

public static function canDelete(Model $record): bool
{
    $user = Auth::user();

    return $user instanceof User && $user->hasRole('admin');
}

}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Models\Group;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Inventory';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //Select subcategory from subcategories table
                Select::make('subcategory_id')
                    ->relationship('subcategory', 'name')
                    ->required()
                    ->label('Subcategory')
                    ->placeholder('Select a subcategory'),
                //Text input for group name
                TextInput::make('name')
                    ->required()
                    ->label('Group Name')
                    ->maxLength(255)
                    ->placeholder('Enter group name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('subcategory.name')
                    ->label('Subcategory')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Group Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }

    //Hide this resource from storekeepers
    public static function shouldRegisterNavigation(): bool
{
    $user = Auth::user();

    // Hide the navigation only for storekeeper, show for others
    if ($user instanceof User && $user->hasRole('storekeeper')) {
        return false;
    }

    return true;
}

    // Permissions
    public static function canViewAny(): bool
    {
        return Auth::check();
    }

    //Permission to create Items
    public static function canCreate(): bool
    {
        return self::userCanManageGroups();
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCanManageGroups();
    }

    public static function canDelete(Model $record): bool
    {
        return self::userCanManageGroups();
    }

    protected static function userCanManageGroups(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasAnyRole(['admin', 'operator']);
    }
}

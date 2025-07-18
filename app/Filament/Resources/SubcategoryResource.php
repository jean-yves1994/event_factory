<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubcategoryResource\Pages;
use App\Filament\Resources\SubcategoryResource\RelationManagers;
use App\Models\Subcategory;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SubcategoryResource extends Resource
{
    protected static ?string $model = Subcategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationGroup = 'Inventory';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //Select category from categories table
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->label('Category')
                    ->placeholder('Select a category'),
                //Text input for subcategory name
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Subcategory Name')
                    ->maxLength(255)
                    ->placeholder('Enter subcategory name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Subcategory Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')   
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListSubcategories::route('/'),
            'create' => Pages\CreateSubcategory::route('/create'),
            'edit' => Pages\EditSubcategory::route('/{record}/edit'),
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
        return self::userCanManageSubcategories();
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCanManageSubcategories();
    }

    public static function canDelete(Model $record): bool
    {
        return self::userCanManageSubcategories();
    }

    protected static function userCanManageSubcategories(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasAnyRole(['admin', 'operator']);
    }
}

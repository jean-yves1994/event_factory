<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-numbered-list';
    protected static ?string $navigationGroup = 'Inventory';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('name')
                    ->required()
                    ->label('Category Name')
                    ->maxLength(255)
                    ->placeholder('Enter category name'),]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('name')
                    ->label('Category Name')
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

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
        return self::userCanManageCategories();
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCanManageCategories();
    }

    public static function canDelete(Model $record): bool
    {
        return self::userCanManageCategories();
    }

    protected static function userCanManageCategories(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasAnyRole(['admin', 'operator']);
    }
}

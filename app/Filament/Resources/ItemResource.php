<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Group;
use App\Models\Item;
use App\Models\Subcategory;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'Inventory';


    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Item Details')
                ->schema([
                    TextInput::make('name')
                        ->label('Item Name')
                        ->required(),

                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set) {
                            $set('subcategory_id', null);
                            $set('group_id', null);
                        }),

                    Select::make('subcategory_id')
                        ->label('Subcategory')
                        ->options(fn (callable $get) =>
                            Subcategory::where('category_id', $get('category_id'))->pluck('name', 'id')
                        )
                        ->required()
                        ->reactive(),

                    Select::make('group_id')
                        ->label('Group (Optional)')
                        ->options(fn (callable $get) =>
                            Group::where('subcategory_id', $get('subcategory_id'))->pluck('name', 'id')
                        )
                        ->nullable(),

                    TextInput::make('model')
                        ->label('Model (Optional)')
                        ->nullable(),

                    TextInput::make('serial_number')
                        ->label('Serial Number (Optional)')
                        ->nullable(),

                    Select::make('unit')
                        ->options([
                            'Kg' => 'Kg',
                            'Cartons' => 'Cartons',
                            'PC' => 'PC',
                            'L' => 'L',
                            'M' => 'M',
                            'Sqm' => 'Sqm',
                        ])
                        ->default('PC')
                        ->required(),

                    TextInput::make('quantity')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->default(1),

                    TextInput::make('flight_case')
                        ->label('Flight Case (Optional)')
                        ->nullable(),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'available' => 'Available',
                            'damaged' => 'Damaged',
                            'lost' => 'Lost',
                        ])
                        ->default('available'),

                    TextInput::make('remarks')->nullable(),

                    FileUpload::make('image')
                        ->label('Item Image')
                        ->image()
                        ->disk('public')
                        ->directory('items')
                        ->preserveFilenames()
                        ->enableOpen()
                        ->enableDownload(),

                ])
                ->columns(2),
        ]);
    }


public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('id')->sortable()->searchable(),
            TextColumn::make('name')->sortable()->searchable(),
            TextColumn::make('category.name')->label('Category')->sortable()->searchable(),
            TextColumn::make('subcategory.name')->label('Subcategory')->sortable()->searchable(),
            TextColumn::make('model')->sortable()->searchable(),
            TextColumn::make('serial_number')->sortable()->searchable(),
            TextColumn::make('unit')->sortable()->searchable(),
            TextColumn::make('quantity')->sortable()->searchable(),
            TextColumn::make('status')
                ->badge()
                ->sortable()
                ->searchable()
                ->colors([
                    'success' => 'available',
                    'danger' => fn ($state): bool => $state !== 'available',
                ]),
            TextColumn::make('flight_case')->sortable()->searchable(),
            ImageColumn::make('image')
                ->disk('public')
                ->label('Image')
                ->size(50)
                ->circular()
                ->visibility('public'),
        ])
        ->filters([
            SelectFilter::make('category_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->searchable()
                ->preload(),

            SelectFilter::make('subcategory_id')
                ->label('Subcategory')
                ->options(function () {
                    $categoryId = request()->input('tableFilters.category_id');

                    return Subcategory::when($categoryId, fn ($query) => 
                        $query->where('category_id', $categoryId)
                    )->pluck('name', 'id')->toArray();
                })
                ->searchable()
                ->preload(),

            SelectFilter::make('group_id')
                ->label('Group')
                ->options(function () {
                    $subcategoryId = request()->input('tableFilters.subcategory_id');

                    return Group::when($subcategoryId, fn ($query) => 
                        $query->where('subcategory_id', $subcategoryId)
                    )->pluck('name', 'id')->toArray();
                })
                ->searchable()
                ->preload(),
        ])
        ->defaultSort('id', 'desc')
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
            
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
        return self::userCanManageItems();
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCanManageItems();
    }

    public static function canDelete(Model $record): bool
    {
        return self::userCanManageItems();
    }

    protected static function userCanManageItems(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasAnyRole(['admin', 'operator']);
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use App\Models\Item;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = "heroicon-o-calendar-days";
    protected static ?string $navigationGroup = "Event Management";

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make("event_name")
                ->required()
                ->label("Event Name"),
            DatePicker::make("event_date")
                ->required()
                ->label("Event Date"),
            TextInput::make("event_location")
                ->required()
                ->label("Event Location"),
            TextInput::make("customer")
                ->required()
                ->label("Customer"),
            TextInput::make("responsible_person_name")
                ->required()
                ->label("Responsible Person Name"),
            TextInput::make("responsible_person_phone")
                ->required()
                ->label("Responsible Person Phone"),
            TextInput::make("responsible_person_email")
                ->required()
                ->label("Responsible Person Email"),
            Select::make("urgency")
                ->options([
                    "low" => "Low",
                    "medium" => "Medium",
                    "high" => "High",
                ])
                ->required()
                ->label("Urgency"),
            Textarea::make("notes")->label("Notes")->columnspan("full"),

            // Requisition Fields - Bind to Event's Requisition
            DatePicker::make("requisition.expected_pickup_date")
                ->required()
                ->label("Expected Pickup Date")
                ->default(
                    fn($record) => $record->requisition->expected_pickup_date ??
                        null
                ),
            DatePicker::make("requisition.expected_return_date")
                ->required()
                ->label("Expected Return Date")
                ->default(
                    fn($record) => $record->requisition->expected_return_date ??
                        null
                ),

            // Repeater for Items - Bind to Event's Requisition Items
            Forms\Components\Section::make('Add Event Items')
                ->schema([
                    Forms\Components\View::make('filament.forms.components.event-item-selector-wrapper')
                        ->columnSpan('full'),
                ])
                ->columnSpan('full'),
                Forms\Components\Hidden::make('items')
    ->dehydrated()
    ->default(fn (\App\Filament\Resources\EventResource\Pages\CreateEvent $livewire) => json_encode($livewire->addedItems))



        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Your existing table columns
                Tables\Columns\TextColumn::make("id")
                    ->label("ID")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("event_name")
                    ->label("Event Name")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("event_date")
                    ->label("Event Date")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("event_location")
                    ->label("Event Location")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("customer")
                    ->label("Customer")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("responsible_person_name")
                    ->label("Responsible Person Name")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make("responsible_person_phone")
                    ->label("Responsible Person Phone")
                    ->sortable()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->filters([
                // Your filters
            ]);
    }

    protected static function booted()
    {
        static::created(function ($event) {
            $event->requisition()->create([
                "event_id" => $event->id,
                "expected_pickup_date" => $event->expected_pickup_date, // Use the value from the form
                "expected_return_date" => $event->expected_return_date, // Use the value from the form
                "status" => "pending", // initial status
            ]);
        });
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListEvents::route("/"),
            "create" => Pages\CreateEvent::route("/create"),];
        if ($user instanceof User && $user->hasRole("storekeeper")) {
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
        return self::userCanManageEvents();
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCanManageEvents();
    }

    public static function canDelete(Model $record): bool
    {
        return self::userCanManageEvents();
    }

    protected static function userCanManageEvents(): bool
    {
        $user = Auth::user();
        return $user instanceof User &&
            $user->hasAnyRole(["admin", "operator"]);
    }
}

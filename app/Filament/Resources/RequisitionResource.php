<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequisitionResource\Pages;
use App\Models\Requisition;
use App\Models\StockMovement;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RequisitionResource extends Resource
{
    protected static ?string $model = Requisition::class;
    protected static ?string $navigationIcon = "heroicon-o-arrow-right-start-on-rectangle";
    protected static ?string $navigationGroup = 'Event Management';


    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make("event_id")
                ->relationship("event", "event_name")
                ->label("Event Name")
                ->required(),

            Select::make("event_id")
                ->relationship("event", "event_date")
                ->label("Event Date")
                ->required(),

            DatePicker::make("expected_pickup_date")
                ->required()
                ->label("Expected Pickup Date"),

            DatePicker::make("expected_return_date")
                ->required()
                ->label("Expected Return Date"),

            // Items Repeater
            Section::make()->schema([
                Repeater::make("items")
                    ->relationship("items")
                    ->schema([
                        Select::make("item_id")
                            ->label("Item")
                            ->options(\App\Models\Item::pluck("name", "id"))
                            ->searchable()
                            ->required(),

                        TextInput::make("quantity")
                            ->required()
                            ->label("Quantity")
                            ->numeric(),
                    ])
                    ->columns(2)
                    ->label("Items and Quantity")
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("event.event_name")->label("Event"),
                TextColumn::make("status")
                    ->badge()
                    ->colors([
                        "success" => "approved",
                    ]),
                TextColumn::make("expected_return_date"),
                // Display items + quantities
                TextColumn::make("items_list")
                    ->label("Items")
                    ->getStateUsing(function (Requisition $record) {
                        return $record->items
                            ->map(function ($item) {
                                return "{$item->name} ({$item->pivot->quantity})";
                            })
                            ->join(", ");
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make("approve")
                    ->label("Approve")
                    ->color("success")
                    ->visible(
                        fn(Requisition $record) => $record->status === "pending"
                    )
                    ->action(function (Requisition $record) {
                     // Update the requisition status to 'approved'
                     $record->update(['status' => 'approved']);

                     // Load items *with pivot quantity*
                     $items = $record->items()->withPivot('quantity')->get();

                     foreach ($items as $item) {
                         // Debug: dump pivot quantity
                         Log::info('Creating stock movement', [
                             'item_id' => $item->id,
                             'quantity' => $item->pivot->quantity ?? 'NO QUANTITY',
                         ]);
                     
                         StockMovement::create([
                             'item_id' => $item->id,
                             'requisition_id' => $record->id,
                             'quantity' => $item->pivot->quantity ?? 1, // fallback to 1 if null
                             'status' => 'pending', // must be one of ['issued', 'returned', 'damaged', 'lost']
                             'action_date' => now(),
                         ]);
                     }
                 
                     Notification::make()
                         ->title('Requisition approved and stock movements created.')
                         ->success()
                         ->send();
                    }),

                    \Filament\Tables\Actions\Action::make("reject")
                    ->label("Reject")
                    ->color("danger")
                    ->visible(
                        fn(Requisition $record) => $record->status === "pending"
                    )
                    ->action(
                        fn(Requisition $record) => $record->update([
                            "status" => "rejected",
                        ])
                    ),
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
            "index" => Pages\ListRequisitions::route("/"),
            "create" => Pages\CreateRequisition::route("/create"),
            "edit" => Pages\EditRequisition::route("/{record}/edit"),
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
        return self::userCanManageRequisitions();
    }

    public static function canEdit(Model $record): bool
    {
        return self::userCanManageRequisitions();
    }

    public static function canDelete(Model $record): bool
    {
        return self::userCanManageRequisitions();
    }

    protected static function userCanManageRequisitions(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasAnyRole(['admin', 'operator']);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'status', 'expected_pickup_date', 'expected_return_date'
    ];

    public function event()
{
    return $this->belongsTo(Event::class);
}
public function stockMovements()
{
    return $this->hasMany(StockMovement::class);
}
// Relationships with Items
public function items()
    {
        return $this->belongsToMany(Item::class)->withPivot('quantity');
    }
 public function requisitionItems()
{
    return $this->hasMany(RequisitionItem::class);
} 

    public function getIsOverdueAttribute()
    {
        return now()->greaterThan($this->expected_return_date)
            && $this->items()->exists();
    }

    public function approve()
{
    // Mark requisition as approved
    $this->status = 'approved';
    $this->save();

    // Create stock movements
    foreach ($this->items as $item) {
        \App\Models\StockMovement::create([
            'requisition_id' => $this->id,
            'item_id' => $item->id,
            'status' => 'issued', // Mark as issued
            'action_date' => now(),
        ]);

        // Decrease stock from item
        $item->decrement('quantity', $item->pivot->quantity);
    }
}

}


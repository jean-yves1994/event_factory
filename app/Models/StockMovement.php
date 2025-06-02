<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id', 'item_id', 'status', 'action_date', 'quantity','good_condition', 'damaged_quantity', 'lost_quantity',
    ];
    protected $attributes = [
        'good_condition' => 0,
    ];
    

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }
    
}


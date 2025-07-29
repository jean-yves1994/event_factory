<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemReturn extends Model
{
    protected $fillable = [
        'stock_movement_id',
        'good_condition',
        'damaged_quantity',
        'lost_quantity',];

    public function stockMovement()
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}



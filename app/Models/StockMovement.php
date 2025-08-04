<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'requisition_id',
        'quantity',
        'status',
        'action_date',
    ];
    protected $attributes = [

    ];
    

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }
    public function itemReturns()
    {
        return $this->hasMany(ItemReturn::class);
    }

    public function getEventNameAttribute()
{
    return $this->event_name ?? $this->requisition?->event?->event_name;
}


public function getEventDateAttribute()
{
    return $this->requisition?->event?->event_date;
}


    
}


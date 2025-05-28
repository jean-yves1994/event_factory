<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'category_id', 'subcategory_id', 'group_id', 'model', 'status',
        'serial_number', 'unit', 'quantity', 'flight_case','remarks','image',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    //Relationships with category, subcategory, and group
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function requisitionItems()
{
    return $this->hasMany(RequisitionItem::class);
}

    public function availableQuantity()
    {
        $issued = $this->stockMovements()->where('status', 'issued')->sum('quantity');
        $returned = $this->stockMovements()->where('status', 'returned')->sum('quantity');
        return $this->quantity - ($issued - $returned);
    }
}

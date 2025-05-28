<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    //
    protected $fillable = [
        'name',
        'subcategory_id',
    ];
    //Relationship with subcategories
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }   

}

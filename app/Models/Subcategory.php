<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    //
    protected $fillable = [
        'name',
        'category_id',
    ];
    //Relationship with categories
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    //Relationships with groups
    public function groups()
    {
        return $this->hasMany(Group::class);
    }
    
}

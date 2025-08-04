<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $fillable = [
        'event_name', 'organizer', 'event_date', 'event_location','customer','responsible_person_name',
        'responsible_person_phone', 'responsible_person_email', 'urgency','notes'
    ];



    public function requisition()
{
    return $this->hasOne(Requisition::class);
}
}

<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{

    public $timestamps = false;

    protected $table = 'options';
    protected $guarded = ['id'];

    public function scopeText($query, $option)
    {
        return $query->where('key', $option)->first()->value ?? null;
    }

}
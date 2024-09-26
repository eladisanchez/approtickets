<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use ApproTickets\Enums\OptionType;

class Option extends Model
{

    public $timestamps = false;

    protected $table = 'options';
    protected $guarded = ['id'];

    protected $casts = [
        'type' => OptionType::class,
    ];

    public function scopeText($query, $option)
    {
        return $query->where('key', $option)->first()->value ?? null;
    }

}
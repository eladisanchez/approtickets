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

    public static function text(string $key): ?string
    {
        $option = static::where('key', $key)->first();
        if (!$option) {
            return null;
        }
        return $option->value;
    }
}
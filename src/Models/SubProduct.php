<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
 
class SubProduct extends Pivot
{

    public $table = 'products_packs';

    public $timestamps = false;

    public $casts = [
        'pricezone' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }
 
    public function pack(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}


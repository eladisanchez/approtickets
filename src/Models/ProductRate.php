<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
 
class ProductRate extends Pivot
{

    public $timestamps = false;
    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }
 
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}


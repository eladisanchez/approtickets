<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venue extends Model
{

    use SoftDeletes;

    protected $table = 'venues';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'stage' => 'boolean',
        'seats' => 'array'
    ];

    protected static function booted()
    {
        static::creating(function ($venue) {
            $venue->seats ??= [];
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'venue_id', 'id')
            ->orderBy('order');
    }

    public function duplicate()
    {
        $newVenue = $this->replicate([
            'products_count'
        ]);
        $newVenue->name = "{$this->name} (còpia)";
        $newVenue->save();
        return $newVenue;
    }

}
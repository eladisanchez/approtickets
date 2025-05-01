<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Refund extends Model
{

    protected $table = 'refunds';
    protected $guarded = ['id'];
    protected $hidden = ['updated_at'];
    protected $with = ['order'];
    protected $casts = [
        'session_canceled' => 'datetime',
        'session_new' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($refund) {
            $refund->hash = Str::random(32);
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function bookings()
    {
        return $this->order->bookings->where('refund', 1);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
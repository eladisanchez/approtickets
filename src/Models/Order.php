<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model {

    use SoftDeletes;

	protected $table = 'orders';
	protected $guarded = ['id','product_id'];
	protected $hidden = ['updated_at'];
    protected $append = ['number'];

    public function booted()
    {
        static::deleting(function($order) {
            foreach ($order->bookings()->get() as $booking) {
                $booking->delete();
            }
        });
        static::restoring(function($order) {
            foreach ($order->bookings()->get() as $booking) {
                $booking->restore();
            }
        });
    }

    public function bookings()
    {
        // Check if order is trashed
        if ($this->trashed()) {
            return $this->hasMany(Booking::class)->withTrashed();
        }
        return $this->hasMany(Booking::class);
    }

    public function user()
    {
    	return $this->belongsTo(User::class,'user_id');
    }

    public function getLinkpdfAttribute()
    {
        return '';
    }

    public function getNumberAttribute()
    {
        return str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function organizers()
    {

        $users = collect();
        foreach ($this->bookings as $res) {
            if($res->product->organizer)
            $users->push($res->product->organizer);
        }
        return $users->unique();

    }

    protected static function boot() 
    {
        parent::boot();
        static::deleting(function($order) {
            foreach ($order->bookings()->get() as $booking) {
                $booking->delete();
            }
        });
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class)->where('refund',1);
    }

    public function scopeIsPaid($query)
    {
        return $query->where('paid',1)->orWhere('payment','credit');
    }

}
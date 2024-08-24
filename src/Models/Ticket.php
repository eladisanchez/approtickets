<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Cart;
use ApproTickets\Models\Booking;

class Ticket extends Model
{

    protected $table = 'products_tickets';
    public $timestamps = false;
    protected $appends = ['available', 'cartSeats', 'bookedSeats'];
    protected $guarded = ['id'];
    protected $casts = [
        'day' => 'datetime:Y-m-d',
        'hour' => 'datetime:H:i',
        'seats' => 'array'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function bookings()
    {

        $product = $this;

        $bookings = Booking::with('order')->where(function ($query) use ($product) {
            $query->where('product_id', $product->product_id)
                ->orWhere('product_id', $product->parent_id);
        })->where('day', $this->day)
            ->where('hour', $this->hour->format('H:i:s'))
            ->whereHas('order', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->sum('tickets');

        return $bookings;

    }

    public function getCartSeatsAttribute()
    {
        $cartItems = Booking::where('product_id', $this->product_id)
            ->where('day', $this->day)
            ->where('hour', $this->hour)
            ->where('order_id', NULL)
            ->where('session', session()->getId())
            ->get(['seat']);
        $seats = $cartItems->map(function ($booking) {
            return ['s' => $booking->seat, 'f' => $booking->row];
        })->toArray();
        return $seats;
    }

    public function getBookedSeatsAttribute()
    {
        $bookings = Booking::where('product_id', $this->product_id)
            ->where('day', $this->day)
            ->where('hour', $this->hour)
            //->where('session', '!=', session()->getId())
            ->get(['seat']);
        $seats = $bookings->map(function ($booking) {
            return ['s' => $booking->seat, 'f' => $booking->row];
        })->toArray();
        return $seats;

    }

}

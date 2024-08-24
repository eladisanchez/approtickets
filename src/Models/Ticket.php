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

    // Això resta les del cistell a les disponibles
    public function getAvailableAttribute()
    {

        $value = $this->tickets - $this->bookings();

        $self = $this;
        $cistell = Cart::search(function ($k, $v) use ($self) {
            return $k->model && $k->model->id == $self->product_id && $k->options->day == $self->day->format('Y-m-d') && $k->options->hour == $self->hour->format('H:i:s');
        });

        $i = 0;

        if ($cistell) {
            foreach ($cistell as $row) {
                $prod = $row;
                $value -= $prod->qty;
                $i++;
            }
        }

        return $value;


    }


    public function getCartSeatsAttribute()
    {
        $cartItems = Booking::where('product_id', $this->producte_id)
            ->where('day', $this->day->toDateString())
            ->where('hour', $this->hour)
            ->where('order_id', NULL)
            ->where('session', session()->getId())
            ->pluck('seat');
        $cartItems->map(function ($item) {
            return json_decode($item);
        });
        return $cartItems;
    }

    public function getBookedSeatsAttribute()
    {
        $bookings = Booking::where('product_id', $this->product_id)
            ->where('day', $this->day)
            ->where('hour', $this->hour)
            ->where('session', '!=', session()->getId())
            ->pluck('seat');
        $bookings->map(function ($item) {
            return json_decode($item);
        });
        return $bookings;

    }

}

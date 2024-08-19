<?php

namespace Approtickets\Models;

use Illuminate\Database\Eloquent\Model;
use Cart;
use Approtickets\Models\Booking;

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

        $value = $this->entrades - $this->bookings();

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


    public function getCartSeatsAttribute($value)
    {

        $self = $this;

        $cistell = array();

        // Al cistel
        $rcistell = Cart::search(function ($i) use ($self) {
            return $i->id == $self->producte_id &&
                $i->options->day == $self->day->toDateString() &&
                $i->options->hour == $self->hour->toTimeString();
        });
        if ($rcistell) {
            foreach ($rcistell as $fila) {
                $cistell[] = $fila->options->seat;
            }
        }

        // Als packs
        $packscistell = Cart::search(function ($i) use ($self) {
            return is_array($i->options->bookings);
        });
        foreach ($packscistell as $fila) {
            foreach ($fila->options->bookings as $booking) {
                if (
                    $booking["product"] == $self->producte_id &&
                    $booking["day"] == $self->dia->toDateString() &&
                    $booking["hour"] == $self->hour->toTimeString()
                )
                    $cistell[] = $booking["seat"];
            }
        }

        return json_encode($cistell);

    }

    public function getBookedSeatsAttribute($value)
    {
        $bookings = Booking::where('product_id', $this->product_id)
            ->where('day', $this->day)
            ->where('hour', $this->hour)
            ->whereHas('order', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->pluck('seat')->toArray();
        return $bookings;

    }

}

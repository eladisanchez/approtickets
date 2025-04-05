<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use ApproTickets\Models\Booking;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mail;
use ApproTickets\Mail\RefundMail;

class Ticket extends Model
{

    protected $table = 'products_tickets';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'day' => 'datetime:Y-m-d',
        'hour' => 'datetime:H:i',
        'seats' => 'array'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'product_id', 'product_id')
            ->where('day', $this->day)
            ->where('hour', $this->hour);
    }

    public function getBookingsTotalAttribute()
    {
        return $this->bookings->sum('tickets');
    }

    public function getAvailableAttribute()
    {
        return $this->tickets - $this->bookingsTotal;
    }

    public function getCartSeatsAttribute()
    {
        $cartItems = $this->bookings
            ->where('order_id', NULL)
            ->where('session', session()->getId())
            ->values();
        return $cartItems->map(function ($booking) {
            return ['s' => $booking->seat, 'f' => $booking->row];
        })->toArray();
    }

    public function getBookedSeatsAttribute()
    {
        $cartSeats = collect($this->cart_seats);

        return $this->bookings
            ->reject(function ($booking) use ($cartSeats) {
                return $cartSeats->contains(fn($cart) => $cart['s'] === $booking->seat && $cart['f'] === $booking->row);
            })
            ->map(function ($booking) {
                return ['s' => $booking->seat, 'f' => $booking->row];
            })
            ->toArray();
    }

    public function cancel(
        string|null $newDate = null,
        bool $canRefund = false
    ) {
        $sessionCanceled = null;
        if ($newDate) {
            $sessionCanceled = "{$this->day} {$this->hour}";
            $this->day = date('Y-m-d', strtotime($newDate));
            $this->hour = date('H:i:s', strtotime($newDate));
        }
        $this->save();

        $bookings = Booking::where("product_id", $this->product_id)
            ->where("day", $this->day)
            ->where("hour", $this->hour)
            ->get()
            ->groupBy('order_id');

        if (count($bookings) > 0) {
            foreach ($bookings as $orderId => $orderBookings) {
                $amountRefund = 0;
                foreach ($orderBookings as $booking) {
                    if ($canRefund) {
                        $booking->refund = 1;
                    }
                    if ($newDate) {
                        $booking->day = $this->day;
                        $booking->hour = $this->hour;
                    }
                    $booking->save();
                    $amountRefund += $booking->tickets * $booking->price;
                }
                $order = Order::find($orderId);
                if ($order && $order->tpv_id) {
                    $refund = new Refund([
                        'product_id' => $this->product_id,
                        'order_id' => $order->id,
                        'total' => $amountRefund,
                        'session_canceled' => $sessionCanceled,
                        'session_new' => $newDate
                    ]);
                    Mail::send(new RefundMail($refund))->to($order->email);
                }
            }
        }

    }

}

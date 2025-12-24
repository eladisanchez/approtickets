<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use ApproTickets\Models\Booking;
use ApproTickets\Models\Refund;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mail;
use Log;
use ApproTickets\Mail\RefundMail;
use DB;
use Carbon\Carbon;

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

    protected static function booted()
    {
        static::created(function ($ticket) {
            if ($ticket->product->venue_id) {
                $ticket->seats = $ticket->product->venue->seats;
                $ticket->save();
            }
        });
    }

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

    public function getCartAttribute()
    {
        return $this->bookings
            ->where('order_id', NULL)
            ->where('session', session()->getId())
            ->sum('tickets');
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
                return $cartSeats->contains(fn($cart) => $cart['s'] === $booking->seat && $cart['f'] === $booking->row) || $booking->refund;
            })
            ->map(function ($booking) {
                return ['s' => $booking->seat, 'f' => $booking->row];
            })
            ->values()
            ->toArray();
    }

    public function cancel(?string $newDate = null)
    {
        $day = $this->day instanceof Carbon ? $this->day : Carbon::parse($this->day);
        $hour = $this->hour instanceof Carbon ? $this->hour : Carbon::parse($this->hour);

        $sessionCanceled = "{$day->format('Y-m-d')} {$hour->format('H:i:s')}";
        $oldDay = $this->day;
        $oldHour = $this->hour;

        try {
            DB::transaction(function () use ($newDate, $oldDay, $oldHour, $sessionCanceled) {
                if ($newDate) {
                    $newDateCarbon = Carbon::parse($newDate);
                    $this->day = $newDateCarbon->format('Y-m-d');
                    $this->hour = $newDateCarbon->format('H:i:s');
                }
                $this->canceled = 1;
                $this->save();

                $bookings = Booking::with('order')
                    ->where("product_id", $this->product_id)
                    ->where("day", $oldDay)
                    ->where("hour", $oldHour)
                    ->get()
                    ->groupBy('order_id');

                if ($bookings->isNotEmpty()) {
                    foreach ($bookings as $orderId => $orderBookings) {
                        $amountRefund = 0;
                        foreach ($orderBookings as $booking) {
                            $booking->refund = 1;
                            if ($newDate) {
                                $booking->day = $this->day;
                                $booking->hour = $this->hour;
                            }
                            $booking->save();
                            $amountRefund += $booking->tickets * $booking->price;
                        }

                        $order = $orderBookings->first()->order;

                        if ($order && $order->tpv_id) {
                            $refund = Refund::create([
                                'product_id' => $this->product_id,
                                'order_id' => $order->id,
                                'total' => $amountRefund,
                                'session_canceled' => $sessionCanceled,
                                'session_new' => $newDate
                            ]);

                            if ($order->email) {
                                try {
                                    Mail::to($order->email)->send(new RefundMail($refund));
                                } catch (\Exception $e) {
                                    Log::error("Error sending refund email for order {$order->id}: " . $e->getMessage());
                                }
                            }
                        }
                    }
                }
            });
            return true;
        } catch (\Exception $e) {
            Log::error("Error cancelling ticket session {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

}

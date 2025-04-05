<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Extract extends Model
{

    use SoftDeletes;
    protected $table = 'extracts';
    protected $fillable = ['date_start', 'date_end', 'user_id', 'product_id'];
    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date'
    ];
    protected $append = [
        'total_sales'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function setDateStartAttribute($value)
    {
        $this->attributes['date_start'] = $value ?? new Carbon('first day of this month');
    }
    public function setDateEndAttribute($value)
    {
        $this->attributes['date_end'] = $value ?? new Carbon('last day of this month');
    }

    public function getSalesAttribute()
    {
        $bookings = Booking::with('rate')->with('product')
            ->whereDate('created_at', '>=', $this->date_start)
            ->whereDate('created_at', '<=', $this->date_end)
            ->whereHas("order", function ($q) {
                $q->where('payment', 'card')->where('paid', 1);
            });
        if ($this->user_id) {
            $bookings->whereHas('product', function ($q) {
                $q->where("user_id", $this->user_id);
            });
        }
        if ($this->producte_id) {
            $bookings->where('product_id', $this->producte_id);
        }
        $bookings = $bookings->get()->groupBy(['product.title', 'rate.title'], $preserveKeys = true);
        $sales = [];
        foreach ($bookings as $product => $rate):
            foreach ($rate as $t => $bookings):
                $total = $bookings->reduce(function ($carry, $item) {
                    return $carry + $item->tickets * $item->price;
                });
                $refund = $bookings->reduce(function ($carry, $item) {
                    if ($item->refund) {
                        return $carry + $item->tickets * $item->price;
                    } else {
                        return $carry;
                    }
                });
                $settle = $total - $refund;
                $sales[] = [
                    'product' => $product,
                    'rate' => $t,
                    'tickets' => $bookings->sum('tickets'),
                    'total' => $total,
                    'refund' => $refund,
                    'settle' => $settle
                ];
            endforeach;
        endforeach;
        return $sales;
    }

    public function getTotalSalesAttribute()
    {
        $bookings = Booking::with('Rate')->with('product')
            ->whereDate('created_at', '>=', $this->date_start)
            ->whereDate('created_at', '<=', $this->date_end)
            ->whereHas("order", function ($q) {
                $q->where('payment', 'card')->where('paid', 1);
            })
            ->whereHas('product', function ($q) {
                if ($this->producte_id) {
                    $q->where("id", $this->product_id);
                } else {
                    $q->where("user_id", $this->user_id);
                }
            })->get();
        $total = $bookings->reduce(function ($total, $item) {
            return $total + $item->tickets * $item->price;
        });
        return $total;
    }

    public function getTotalRefundsAttribute()
    {
        $bookings = Booking::with('Rate')->with('product')
            ->whereDate('created_at', '>=', $this->date_start)
            ->whereDate('created_at', '<=', $this->date_end)
            ->where('refund', 1)
            ->whereHas("order", function ($q) {
                $q->where('payment', 'card')->where('paid', 1);
            })
            ->whereHas('product', function ($q) {
                if ($this->product_id) {
                    $q->where("id", $this->product_id);
                } else {
                    $q->where("user_id", $this->user_id);
                }
            })->get();
        $total = $bookings->reduce(function ($total, $item) {
            return $total + $item->numEntrades * $item->preu;
        });
        return $total;
    }

    public function getTotalAttribute(): float
    {
        return $this->totalSales - $this->totalRefunds;
    }

}
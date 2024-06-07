<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{

    use SoftDeletes;

    protected $table = 'products';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['price', 'pricezone'];
    protected $with = ['organizer', 'rates'];
    protected $attributes = [
        'name' => '',
    ];


    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    protected static function booted()
    {
        static::creating(function ($product) {
            $product->name = Str::slug($product->title);
        });
    }

    public function scopeUrl($query, $url)
    {
        return $query->where('name', $url)->first();
    }

    // Filtra per target
    public function scopeOfTarget($query, $target)
    {
        return $query->where('target', $target);
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function productRates()
    {
        return $this->hasMany(ProductRate::class);
    }

    public function getPriceAttribute($value)
    {

        if (isset($this->pivot->preu)) {
            if ($this->pivot->preu > 0) {
                // Preu amb codi de descompte
                if (Session::has('coupon.p' . $this->id . '_t' . $this->pivot->rate_id)) {
                    $preuDescompte = $this->pivot->preu * (1 - Session::get('coupon.p' . $this->id . '_t' . $this->pivot->rate_id) / 100);
                    return number_format($preuDescompte, 2, ',', '.') . ' €';
                }
                // Preu normal
                else {
                    return number_format($this->pivot->price, 2, ',', '.') . ' €';
                }
            }
            // Gratis
            return trans('textos.gratis');

        }

        return '';


    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Si és un pack
    public function packs()
    {
        return $this->belongsToMany(Product::class, 'products_packs', 'product_id', 'pack_id');
    }
    public function packProducts()
    {
        return $this->belongsToMany(Product::class, 'products_packs', 'pack_id', 'product_id');
    }

    public function tickets(): HasMany
    {
        $datetime = new \DateTime('today');
        return $this->hasMany(Ticket::class)->where('day', '>=', $datetime)->whereNull('cancelled');
        if (Auth::user() && (Auth::user()->hasRole('admin') || Auth::user()->hasRole('organizer')))
            return $this->hasMany(Ticket::class);
        else
            return $this->hasMany(Ticket::class)->where('day', '>', $datetime);

    }

    public function previousTickets()
    {
        $datetime = new \DateTime('today');
        return $this->hasMany(Ticket::class)->where('day', '<', $datetime)->whereNull('cancelled');
    }

    public function rates()
    {
        return $this->belongsToMany(Rate::class, 'product_rate')
            ->using(ProductRate::class)
            ->withPivot('price', 'pricezone');
    }


    public function venue()
    {
        return $this->belongsTo(Venue::class)->where('id', '!=', 0)->withTrashed();
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class)->where('rate_id', $this->rate->id);
    }


    public function availableDays()
    {
        $datetime = new \DateTime('today');
        return Ticket::where('product_id', $this->id)->where('day', '>=', $datetime)->whereNull('cancelled')->groupBy('day')->pluck('day');
    }


    public function allTickets()
    {
        $datetime = new \DateTime('today');
        $tickets = Ticket::where('product_id', $this->id)->where('day', '>=', $datetime)->whereNull('cancelled')->get();
        return $tickets->groupBy(function ($date) {
            return \Carbon\Carbon::parse($date->day)->format('Y-m-d');
        });
    }



    public function ticketsDay($day, $hour = NULL)
    {

        $ref = $this;
        if ($this->parent_id > 0) {
            $ref = $this->parent;
        }

        if ($hour) {
            return $ref->hasMany(Ticket::class)->where('day', $day)->where('hour', $hour)->whereNull('cancelled')->first();
        }

        return $ref->hasMany(Ticket::class)->where('day', $day)->whereNull('cancelled')->get();


    }


    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingsByPaymentMethod($payment)
    {
        $bookings = Booking::where('product_id', $this->id)->whereNull('deleted_at')->whereHas('order', function ($q) use ($payment) {
            $q->where('pagament', $payment)->whereNull('deleted_at');
        })->sum('numEntrades');
        return $bookings;
    }


    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getBuyablePrice($options = null)
    {
        return $this->price;
    }

}

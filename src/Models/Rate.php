<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Rate extends Model
{

    use SoftDeletes;

    protected $table = 'rates';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'name', 'ambdescompte'];

    protected $casts = [
        'pricezone' => 'array',
    ];

    public function product()
    {
        return $this->belongsToMany(Product::class, 'product_rate')
            ->using(ProductRate::class)
            ->withPivot('price');
    }

    public function productRates()
    {
        return $this->hasMany(ProductRate::class);
    }


    public function getPriceAttribute($value)
    {

        if ($this->pivot && $this->pivot->price > 0) {
            // Preu amb codi de descompte
            if (Session::has('codi.p' . $this->pivot->product_id . '_t' . $this->id)) {
                $priceDescompte = $this->pivot->price * (1 - Session::get('codi.descompte') / 100);
                return number_format($priceDescompte, 2, ',', '.') . ' €';
            }
            // Preu normal
            else {
                return number_format($this->pivot->price, 2, ',', '.') . ' €';
            }
        } else {
            // Gratis
            return trans('textos.gratis');
        }

    }

    // public function getPriceZoneAttribute($value)
    // {

    //     if ($this->pivot->pricezone) {
    //         return $this->pivot->pricezone;
    //     } else {
    //         return $this->pivot->price . ',' . $this->pivot->price . ',' . $this->pivot->price . ',' . $this->pivot->price;
    //     }

    // }

    public function getPreuvalueAttribute($value)
    {

        if ($this->pivot && $this->pivot->price > 0) {
            // Preu amb codi de descompte
            if (Session::has('codi.p' . $this->pivot->producte_id . '_t' . $this->id)) {
                $priceDescompte = $this->pivot->price * (1 - Session::get('codi.descompte') / 100);
                return $priceDescompte;
            }
            // Preu normal
            else {
                return $this->pivot->price;
            }
        } else {
            // Gratis
            return trans('textos.gratis');
        }
    }

    public function priceSeat($zona)
    {

        $i = $zona - 1;

        if ($this->pivot && $this->pivot->pricezone) {
            $prices = explode(',', $this->pricezone);
            return $prices[$i];
        } else {
            return $this->pricevalue;
        }

    }


}
<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model {

	protected $table = 'refunds';
	protected $guarded = ['id'];
	protected $hidden = ['updated_at'];
    protected $with = ['order'];
    protected $dates = [
        'day_cancelled',
        'day_new'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function bookings()
    {
        return $this->comanda->bookings->where('refund',1);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


}
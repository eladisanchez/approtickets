<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model {

	protected $table = 'coupons';
	protected $guarded = array('id');
	protected $hidden = array('created_at', 'updated_at');

	public function product()
	{
		return $this->belongsTo(Product::class,'product_id')->select(array('id', 'title'));
	}

	public function Rate()
	{

		return $this->belongsTo(Rate::class,'rate_id')->select(array('id', 'title'));
		
	}

}
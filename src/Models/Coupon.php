<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{

	protected $table = 'coupons';
	protected $guarded = ['id'];
	protected $hidden = ['created_at', 'updated_at'];

	public function product(): BelongsTo
	{
		return $this->belongsTo(Product::class)
			->select(['id', 'title']);
	}

	public function Rate(): BelongsTo
	{
		return $this->belongsTo(Rate::class)
			->select(['id', 'title']);
	}

}
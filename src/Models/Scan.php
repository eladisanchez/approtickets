<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;

class Scan extends Model {

	protected $table = 'scans';
	protected $guarded = array('id');

	public function reserva()
	{
		return $this->belongsTo(Booking::class,'reserva_id');
	}

}
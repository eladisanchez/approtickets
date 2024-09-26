<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Booking extends Model
{

	use SoftDeletes;

	protected $table = 'bookings';
	protected $guarded = ['id', 'product_id'];
	protected $hidden = ['updated_at', 'uniqid'];
	protected $casts = [
		'day' => 'datetime:Y-m-d',
		'hour' => 'datetime:H:i',
		'seat' => 'array'
	];

	public function getHourAttribute($value)
	{
		if ($value)
			return Carbon::createFromFormat('H:i:s', $value)->format('H:i');
	}

	public function getDates()
	{
		return array('day');
	}

	public function product()
	{
		// return $this->belongsTo(Product::class,'product_id')->withTrashed();
		return $this->belongsTo(Product::class, 'product_id');
	}

	public function scans()
	{
		return $this->hasMany(Scan::class);
	}

	public function rate()
	{
		return $this->belongsTo(Rate::class);
	}

	public function getFormattedSeatAttribute()
	{
		if (empty($this->row) || $this->row == 0) {
			return __('Localitat') . ' ' . $this->seat;
		}
		return __('Fila') . ' ' . $this->row . ' ' . __('Seient') . ' ' . $this->seat;
	}

	public function getReducedSeatAttribute()
	{
		if (empty($this->row) || $this->row == 0) {
			if (is_array($this->seat)) {
				return $this->seat['s'] . '/' . $this->seat['f'];
			}
			if (is_numeric($this->seat)) {
				return __('Localitat') . ' ' . $this->seat;
			}
			return false;
		}
		return $this->row . '/' . $this->seat;
	}

	public function getFormattedSessionAttribute()
	{
		return $this->day->format('d/m/Y') . ' ' . $this->hour;
	}

	public function scopeProductDayHour($query, $id, $day, $hour)
	{
		$query->where('product_id', $id)
			->where('day', $day)
			->where('hour', $hour);
	}

	public function order()
	{
		return $this->belongsTo(Order::class)->withTrashed();
	}

	public function qrcode($count)
	{
		$ch = substr(\Hash::make($count . $this->uniqid), -2, 2);
		$qr = base64_encode($ch . '_' . $this->uniqid . '_' . $count . '_' . $this->id);
		return $qr;
	}

	public function qrimage($count)
	{
		$qr = $this->qrcode($count);
		$output = base64_encode(QrCode::format('png')->size(100)->generate($qr));
		return $output;
	}

}

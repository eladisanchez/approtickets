<?php

namespace ApproTickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{

	use SoftDeletes;

	protected $table = 'bookings';
	protected $guarded = ['id', 'product_id'];
	protected $hidden = ['updated_at', 'uid'];
	protected $casts = [
		'day' => 'datetime:Y-m-d',
		'hour' => 'datetime:H:i',
		'seat' => 'array'
	];

	protected static function booted()
	{
		static::deleting(function ($booking) {
			if ($booking->packBookings()->exists()) {
				$booking->packBookings()->delete();
			}
		});
	}

	public function getHourAttribute($value)
	{
		if ($value)
			return Carbon::createFromFormat('H:i:s', $value)->format('H:i');
	}

	// public function getDates()
	// {
	// 	return array('day');
	// }

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function ticket()
	{
		return $this->belongsTo(Ticket::class, 'product_id', 'product_id')
			->where('day', $this->day)
			->where('hour', $this->hour);
	}

	public function scans()
	{
		return $this->hasMany(Scan::class);
	}

	public function rate()
	{
		return $this->belongsTo(Rate::class);
	}

	public function packBookings(): HasMany
	{
		return $this->hasMany(Booking::class, 'pack_booking_id');
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
				return "{$this->seat['s']}/{$this->seat['f']}";
			}
			if (is_numeric($this->seat)) {
				return __('Localitat') . ' ' . $this->seat;
			}
			return false;
		}
		return "{$this->row}/{$this->seat}";
	}

	public function getFormattedSessionAttribute()
	{
		return $this->day?->format('d/m/Y') . ' ' . $this->hour;
	}

	public function getTotalAttribute()
	{
		return $this->price * $this->tickets;
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
		$ch = substr(\Hash::make("{$count}$this->uid"), -2, 2);
		$qr = base64_encode("{$ch}_'{$this->uid}_{$count}_{$this->id}");
		return $qr;
	}

	public function qrimage($count)
	{
		$qr = $this->qrcode($count);
		$output = base64_encode(QrCode::format('png')->size(400)->generate($qr));
		return $output;
	}

}

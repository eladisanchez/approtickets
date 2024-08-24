<?php

namespace ApproTickets\Controllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller as BaseController;
use ApproTickets\Models\Product;
use ApproTickets\Models\Ticket;
use ApproTickets\Models\Booking;
use Cart;
use Session;
use PDF;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ProductController extends BaseController
{

	/**
	 * Product single page
	 */
	public function show($name, $day = NULL, $hour = NULL): View
	{

		$product = Product::withoutGlobalScopes()->where('name', $name)->firstOrFail();
		if (!auth()->user() || auth()->user()->hasRole('organizer') && auth()->user()->id != $product->user_id) {
			if (!$product || !$product->active)
				abort(404);
		}

		$availableDays = $product->availableDays();

		if (!$day && $availableDays->count() == 1) {
			return redirect()->route('product', [
				'name' => $name,
				'day' => $availableDays->first()->format('Y-m-d')
			]);
		}

		return view('product', [
			'product' => $product,
			'availableDays' => $availableDays,
			'tickets' => $day ? $product->ticketsDay($day, $hour) : null,
			'rates' => $product->rates,
			'day' => $day,
			'hour' => $hour,
			'productCart' => $product->inCart()
		]);

	}

	public function availability($id, $day, $hour)
	{

		$cartItems = Cart::instance('shopping')->search(function ($key, $value) use ($id, $day, $hour) {
			return $key->id == $id && $key->options->day == $day && $key->options->hour == $hour;
		});

		// Map cartItems to return only the seat
		$cart = [];
		foreach ($cartItems as $item) {
			$cart[] = $item->options->seat;
		}
		;
		// Get seats from bookings for product, day and hour
		$bookings = Booking::where('product_id', $id)->where('day', $day)->where('hour', $hour)->pluck('seat');

		return response()->json([
			'cart' => $cart,
			'booked' => $bookings
		]);
	}

	// Dia triat
	public function showDay($name, $day)
	{

		$product = Product::with(
			array(
				'entrades' => function ($query) use ($day) {
					$query->where('dia', $day);
				}
			)
		)->where('name', $name)->first();

		return $product;

	}


	public function calendar()
	{
		$today = strtotime("today");
		$nextMonth = date("Y-m-d", strtotime("+2 month", $today));
		$tickets = Ticket::with('product:id,title,summary_ca,place,name,target')->where('day', '>=', $today)->where('day', '<', $nextMonth)->get();
		$cal = [];
		foreach ($tickets as $item):
			if (!$item->product) {
				continue;
			}
			$cal[] = [
				'uid' => uniqid(),
				'title' => $item->product->title,
				'description' => $item->product->summary_ca,
				'start' => $item->day->format('Y-m-d') . ' ' . $item->hour->format('H:i:s'),
				'location' => $item->product->place,
				'url' => route('product', ['name' => $item->product->name, $item->day->format('Y-m-d'), $item->hour->format('H:i')]),
				'color' => $item->product->target == 'individual' ? 'blue' : 'red'
			];
		endforeach;

		return view('calendar', [
			'events' => $cal,
		]);

	}

	public function ics(): JsonResponse
	{

		$tickets = Ticket::with('producte:id,title,resum,place,nom,target')->where('dia', '>=', date('Y-m-d'))->get();
		$events = [];
		foreach ($tickets as $item):

			$event = Event::create($item->producte->title)
				->startsAt(new \DateTime($item->dia->format('Y-m-d') . ' ' . $item->hora->format('H:i:s')))
				->description($item->producte->resum_ca);
			$events[] = $event;

		endforeach;

		$calendar = Calendar::create(config('app.name'))
			->event($events)
			->get();

		return $calendar;
	}


	public function previewPdf($id)
	{
		$product = Product::find($id);
		$pdf = PDF::setOptions(['isRemoteEnabled' => true])->loadView(
			'pdf.contracte-preview',
			array(
				'product' => $product
			)
		);
		return $pdf->stream('preview-' . $id . '.pdf');
	}

	public function image($path)
	{
		$cacheKey = 'image_' . md5($path);
		$cachedImage = Cache::remember($cacheKey, 1, function () use ($path) {
			if (Storage::disk('public')->exists($path)) {
				$directory = explode('/', $path);
				$width = $directory[0] == 'products' ? 600 : 1400;
				$file = Storage::disk('public')->get($path);
				$image = Image::read($file);
				$image->scale($width, null);
				return $image->encode();
			}
			return false;
		});
		if (!$cachedImage) {
			abort(404);
		}
		return response()->make($cachedImage, 200, ['Content-Type' => 'image/jpeg']);
	}


}

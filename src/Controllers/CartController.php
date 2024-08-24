<?php

namespace ApproTickets\Controllers;

use ApproTickets\Models\Rate;
use ApproTickets\Models\Order;
use ApproTickets\Models\Product;
use ApproTickets\Models\Booking;
use DB;
use Gloudemans\Shoppingcart\Facades\Cart;
use Session;
use Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;


class CartController extends BaseController
{


	protected $layout = 'cistell';

	public $cartItems;
	public float $cartTotal;

	protected function initializeCart()
    {
        $this->cartItems = Booking::where('order_id', NULL)
            ->where('session', session()->getId())
            ->get();

        $this->cartTotal = $this->cartItems->sum(function ($item) {
            return $item->price;
        });
    }

	/**
	 * Cart user page
	 */
	public function show(): View
	{
		$this->initializeCart();
		return view('cart', [
			'cart' => $this->cartItems,
			'total' => $this->cartTotal
		]);
	}

	/**
	 * Add standard item
	 */
	public function add(): RedirectResponse
	{

		$this->initializeCart();

		$data = request()->all();

		$product_id = $data['product'] ?? null;
		$product = Product::find($product_id);
		if (!$product) {
			return redirect()->back();
		}

		$day = $data['day'] ?? null;
		$hour = $data['hour'] ?? null;
		$rates = $data['rates'] ?? null;
		$qtys = $data['qty'] ?? null;

		$seats = $data['seats'] ? json_decode($data['seats']) : null;
		if ($seats) {
			$this->addEvent($seats, $product, $data['rate'], $day, $hour);
		}

		if (!$qtys) {
			return redirect()->back();
		}

		// Check min tickets
		$totalqtys = array_sum($qtys);
		if ($totalqtys < $product->min_tickets) {
			return redirect()->back()->with('error', trans('textos.minimEntrades') . $product->min_tickets);
		}

		// Check max tickets in cart
		$ticketsInCart = $this->cartItems->filter(function ($item) use ($product_id, $day, $hour) {
			return $item->product_id == $product_id && $item->day == $day && $item->hour == $hour;
		})->count();
		if ($totalqtys + $ticketsInCart > $product->max_tickets) {
			return redirect()->back()->with('error', trans('textos.max_tickets', ['max' => $product->max_tickets]));
		}

		foreach ($qtys as $i => $qty) {

			if ($qty <= 0)
				continue;

			$rate_id = $rates[$i] ?? null;
			if (!$rate_id)
				continue;

			$rate = Rate::find($rate_id);

			$price = DB::table('product_rate')
				->where('product_id', $product_id)
				->where('rate_id', $rate_id)
				->pluck('price')[0];

			if (Session::has("coupon.p{$product_id}_t{$rate_id}")) {
				$price *= 1 - Session::get('coupon.discount') / 100;
			}

			$booking = new Booking();
			$booking->product_id = $product_id;
			$booking->rate_id = $rate_id;
			$booking->day = $day;
			$booking->hour = $hour;
			$booking->tickets = $qty;
			$booking->price = $price;
			$booking->session = Session::getId();
			$booking->save();

		}

		return redirect()->back()->with('itemAdded', true);

	}


	/**
	 * Add product with seats
	 */
	public function addEvent(array $seats, $product, $rate_id, $day, $hour): RedirectResponse
	{

		$rate = Rate::find($rate_id);
		$price = DB::table('product_rate')
			->where('product_id', $product->id)
			->where('rate_id', $rate->id)
			->pluck('price')[0];
		if (Session::has("coupon.p{$product->id}_t{$rate->id}")) {
			$price *= 1 - Session::get('coupon.discount') / 100;
		}

		$takenSeats = [];

		foreach ($seats as $seat) {

			// Check if seat is available
			$booking = Booking::where('product_id', $product->id)
				->where('day', $day)
				->where('hour', $hour)
				->where('seat', $seat->s)
				->where('row', $seat->f)
				->first();
			if ($booking) {
				$takenSeats[] = $seat;
				continue;
			}

			$booking = new Booking();
			$booking->product_id = $product->id;
			$booking->rate_id = $rate->id;
			$booking->day = $day;
			$booking->hour = $hour;
			$booking->tickets = 1;
			$booking->price = $price;
			$booking->session = Session::getId();
			$booking->seat = intval($seat->s);
			$booking->row = intval($seat->f);
			$booking->save();

		}

		if (count($takenSeats)) {
			return redirect()->back()->with('error', trans('textos.seats_taken', ['seats' => implode(', ', $takenSeats)]));
		}

		return redirect()->back()->with('itemAdded', true);

	}


	/**
	 * Add pack to cart
	 */
	public function addPack(): RedirectResponse
	{

		// Comprovem si el producte és realment un pack
		$pack = Product::find(request()->input('id_pack'));
		if ($pack) {

			// Info del pack que estem reservant guardada a la sessió
			$sessio = Session::get('pack' . $pack->id);

			$qtys = $sessio['qtys'];
			$rates = $sessio['tarifes'];

			$bookings = $sessio['reserves'];

			$i = 0;

			// Per cada una de les quantitats/tarifes
			foreach ($qtys as $qty) {

				// Si s'ha definit una quantitat vàlida
				if ($qty > 0) {

					// Model Rate
					$rate_id = $rates[$i];
					$rate = Rate::find($rate_id);
					$preu = $rate->producte()->where('productes.id', '=', $pack->id)->first()->pivot->preu;

					//$preu = Rate::with('product')->get()->find($rate_id)->producte->find($product->id)->pivot->preu;
					if (Session::has('codi.p' . $pack->id . '_t' . $rate_id)) {
						$preu = $preu * (1 - Session::get('codi.descompte') / 100);
					}

					// Afegir al cistell
					$cartItem = Cart::add(
						$pack->id,
						$pack->title,
						$qty,
						$preu,
						0,
						array(
							'name' => $pack->name,
							'id_producte' => $pack->id,
							'parent' => 0,
							'rate' => $rate,
							'id_Rate' => $rate->id,
							'target' => $pack->target,
							'reserves' => $bookings
						)
					)->associate('\App\Models\Product');

				}

				$i++;

			}

			Session::forget('pack' . $pack->id);

		}

		return redirect()->route('cart');

	}


	public function confirm()
	{

		$order = Order::where('session', Session::getId())
			->where('paid', 0)
			->where('payment', 'card')
			->orderBy('created_at', 'desc')
			->first();
		if ($order) {
			return redirect()->route('checkout-tpv-ko', ['id' => $order->id]);
		}
		$lastOrder = false;
		if (auth()->check()) {
			if (!(auth()->user()->hasRole(['admin', 'organizer']))) {
				$lastOrder = Auth::user()->comandes->last();
			} else {
				$lastOrder = (object) [
					'name' => auth()->user()->username,
					'email' => auth()->user()->email
				];
			}
		}
		return view('order.checkout', [
			'lastOrder' => $lastOrder,
		]);
	}


	/**
	 * Remove item from cart
	 */
	public function removeRow(Request $request): RedirectResponse
	{
		$this->initializeCart();
		$rowId = request()->input('rowid');
		$cartItem = $this->cartItems->filter(function ($item) use ($rowId) {
			return $item->id == $rowId;
		});
		if ($cartItem) {
			$cartItem->map->delete();
		}
		return redirect()->back();
	}

	/**
	 * Delete all cart rows
	 */
	public function destroy(): RedirectResponse
	{
		$this->initializeCart();
		$this->cartItems->map->delete();
		Session::forget('coupon');
		Session::forget('qty');
		return redirect()->back();
	}

}
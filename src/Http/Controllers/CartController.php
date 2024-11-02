<?php

namespace ApproTickets\Http\Controllers;

use ApproTickets\Models\Rate;
use ApproTickets\Models\Order;
use ApproTickets\Models\Product;
use ApproTickets\Models\Booking;
use ApproTickets\Models\ProductRate;
use DB;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\JsonResponse;
use Session;
use Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use ApproTickets\Traits\HandlesErrorResponse;
use ApproTickets\Http\Resources\CartItem;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Database\Eloquent\Collection;


class CartController extends BaseController
{

	use HandlesErrorResponse;

	protected $layout = 'cistell';
	protected Collection $cartItems;
	protected float $cartTotal;

	protected function initializeCart(): void
	{
		$this->cartItems = Booking::with('product')->where('order_id', NULL)
			->where('session', session()->getId())
			->whereNull('pack_booking_id')
			->get();

		$this->cartTotal = $this->cartItems->sum(function ($item) {
			return $item->price * $item->tickets;
		});
	}

	protected function returnCartContent(): JsonResponse
	{
		$this->initializeCart();
		return response()->json([
			'items' => CartItem::collection($this->cartItems),
			'total' => $this->cartTotal
		]);
	}

	/**
	 * Convert cart products to pack when eligible
	 * @param \ApproTickets\Models\Product $product
	 * @param array $rates
	 * @return void
	 */
	protected function convertToPack(Product $product, array $rates): void
	{
		$this->initializeCart();

		foreach ($product->packs as $pack) {

			$packProductIds = $pack->packProducts->modelKeys();
			$packRates = $pack->rates;

			foreach ($packRates as $packRate) {

				if (!in_array($packRate->id, $rates)) {
					continue;
				}

				$packPrice = $packRate->pivot->price;

				$cartRowsEligibleForPack = $this->cartItems->where('rate_id', $packRate->id)
					->whereIn('product_id', $packProductIds)
					->whereNull('pack_booking_id');

				if ($cartRowsEligibleForPack) {

					$cartPackProductsRows = [];
					$cartPackProductIds = [];
					$cartPackProductsQtys = [];

					foreach ($cartRowsEligibleForPack as $row) {
						if (in_array($row->product_id, $packProductIds) && !in_array($row->product_id, $cartPackProductIds)) {
							$cartPackProductsRows[] = $row;
							$cartPackProductsQtys[] = $row->tickets;
							$cartPackProductIds[] = $row->product_id;
						}
					}

					if (count($cartPackProductsRows) == count($packProductIds)) {

						$minPackTickets = min($cartPackProductsQtys);

						$packBooking = Booking::create([
							'product_id' => $pack->id,
							'rate_id' => $packRate->id,
							'tickets' => $minPackTickets,
							'price' => $packPrice,
							'pack_booking_id' => null,
							'session' => Session::getId(),
							'is_pack' => 1
						]);

						for ($i = 0; $i < count($cartPackProductsRows); $i++) {

							Booking::create([
								'product_id' => $cartPackProductsRows[$i]->product_id,
								'rate_id' => $packRate->id,
								'tickets' => $minPackTickets,
								'price' => 0,
								'day' => $cartPackProductsRows[$i]->day,
								'hour' => $cartPackProductsRows[$i]->hour,
								'pack_booking_id' => $packBooking->id,
								'session' => Session::getId(),
							]);

							$qty = $cartPackProductsQtys[$i] - $minPackTickets;

							if ($qty > 0) {
								$cartPackProductsRows[$i]->update([
									'tickets' => $qty,
								]);
							} else {
								$cartPackProductsRows[$i]->delete();
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Cart page (may be optional if cart is integrated in confirmation)
	 * @return \Illuminate\View\View
	 */
	public function show(): View
	{
		$this->initializeCart();
		$pendingOrder = Order::where('session', Session::getId())->where('paid', 0)->first();
		return view('cart', [
			'cart' => $this->cartItems,
			'total' => $this->cartTotal,
			'pendingOrder' => $pendingOrder
		]);
	}

	/**
	 * Add standard product to cart
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
	 */
	public function add(): RedirectResponse|JsonResponse
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

		if (isset($data['seats'])) {
			$seats = is_array($data['seats']) ? $data['seats'] : json_decode($data['seats']);
			return $this->addEvent($seats, $product, $data['rate'], $day, $hour);
		}

		if (!$qtys) {
			return $this->handleErrorResponse(__('approtickets::cart.no_qty'));
		}

		// Check min tickets
		$totalqtys = array_sum($qtys);
		if ($totalqtys < $product->min_tickets) {
			return $this->handleErrorResponse(__('approtickets::cart.min_tickets', ['min' => $product->min_tickets]));
		}

		// Check max tickets in cart
		$ticketsInCart = $this->cartItems->filter(function ($item) use ($product_id, $day, $hour) {
			return $item->product_id == $product_id
				&& $item->day == $day
				&& $item->hour == $hour;
		})->count();

		if ($totalqtys + $ticketsInCart > $product->max_tickets) {
			return $this->handleErrorResponse(__('approtickets::cart.max_tickets', ['max' => $product->max_tickets]));
		}

		foreach ($qtys as $i => $qty) {

			if ($qty <= 0)
				continue;

			$rate_id = $rates[$i] ?? null;
			if (!$rate_id)
				continue;

			$price = DB::table('product_rate')
				->where('product_id', $product_id)
				->where('rate_id', $rate_id)
				->pluck('price')[0];

			if (Session::has("coupon.p{$product_id}_t{$rate_id}")) {
				$price *= 1 - Session::get('coupon.discount') / 100;
			}

			Booking::updateOrCreate([
				'product_id' => $product_id,
				'rate_id' => $rate_id,
				'day' => $day,
				'hour' => $hour,
				'session' => Session::getId(),
				'order_id' => null,
			], [
				'tickets' => DB::raw('tickets + ' . $qty),
				'price' => $price
			]);

		}

		if ($product->packs->count() > 0) {
			$this->convertToPack($product, $rates);
		}

		if (request()->wantsJson()) {
			return $this->returnCartContent();
		}

		return redirect()->back()->with('itemAdded', true);

	}

	/**
	 * Add event product to cart
	 * @param array $seats
	 * @param mixed $product
	 * @param mixed $rate_id
	 * @param mixed $day
	 * @param mixed $hour
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
	 */
	public function addEvent(array $seats, $product, $rate_id, $day, $hour): RedirectResponse|JsonResponse
	{

		$rate = Rate::find($rate_id);

		$productRate = ProductRate::where('product_id', $product->id)
			->where('rate_id', $rate->id)->first();

		$generalPrice = $productRate->price;

		$takenSeats = [];

		foreach ($seats as $seat) {

			if (is_array($seat)) {
				$seat = (object) $seat;
			}

			$price = $productRate->pricezone ? $productRate->pricezone[$seat->z] : $generalPrice;
			if (Session::has("coupon.p{$product->id}_t{$rate->id}")) {
				$price *= 1 - Session::get('coupon.discount') / 100;
			}

			// Check if seat is available
			$booking = Booking::where('product_id', $product->id)
				->where('day', $day)
				->where('hour', $hour)
				->where('seat', $seat->s)
				->where('row', $seat->f)
				->first();
			if ($booking) {
				$takenSeats[] = [$seat->s, $seat->f];
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
			$this->handleErrorResponse(__('approtickets::cart.taken_seats'));
		}

		if (request()->wantsJson()) {
			return $this->returnCartContent();
		}

		return redirect()->back()->with('itemAdded', true);

	}


	// TODO: Add pack to cart
	/**
	 * Add pack to cart
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
	 */
	public function addPack(): RedirectResponse|JsonResponse
	{

		$pack = Product::find(request()->input('id_pack'));
		if ($pack) {

			$session = session()->get("pack{$pack->id}");

			$qtys = $session['qtys'];
			$rates = $session['rates'];

			$bookings = $session['bookings'];

			foreach ($qtys as $i => $qty) {

				if ($qty > 0) {

					// Model Rate
					$rate_id = $rates[$i];
					$rate = Rate::find($rate_id);
					$price = $rate->product()->where('products.id', '=', $pack->id)->first()->pivot->price;

					if (session()->has("code.p{$pack->id}_r{$rate_id}")) {
						$price *= 1 - session()->get('code.discount') / 100;
					}

					$booking = new Booking();
					$booking->product_id = $pack->id;
					$booking->rate_id = $rate_id;
					$booking->tickets = $qty;
					$booking->price = $price;
					$booking->session = Session::getId();
					$booking->save();

					// Afegir al cistell
					$cartItem = Cart::add(
						$pack->id,
						$pack->title,
						$qty,
						$price,
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

		if (request()->wantsJson()) {
			return $this->returnCartContent();
		}

		return redirect()->route('cart');

	}

	/**
	 * Confirmation page
	 * @return \Illuminate\View\View|\Inertia\Response
	 */
	public function confirm(): View|InertiaResponse|RedirectResponse
	{

		$order = Order::where('session', Session::getId())
			->where('paid', 0)
			->where('payment', 'card')
			->orderBy('created_at', 'desc')
			->first();
		// if ($order) {
		// 	return redirect()->route('order.error', [
		// 		'session' => Session::getId(),
		// 		'id' => $order->id
		// 	]);
		// }
		$lastOrder = false;
		if (auth()->check()) {
			$lastOrder = !(auth()->user()->hasRole(['admin', 'organizer'])) ?
				Auth::user()->comandes->last() :
				(object) [
					'name' => auth()->user()->name,
					'email' => auth()->user()->email
				];
		}
		if (config('approtickets.inertia')) {
			return Inertia::render('Checkout', [
				'loggedIn' => auth()->check(),
				'lastOrder' => $lastOrder,
			]);
		}
		return view('order.checkout', [
			'lastOrder' => $lastOrder,
		]);
	}

	/**
	 * Remove row from cart
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
	 */
	public function removeRow(Request $request): RedirectResponse|JsonResponse
	{
		$cartItem = Booking::where('session', Session::getId())
			->where('order_id', NULL)
			->where('id', $request->input('rowId'))
			->first();
		if ($cartItem) {
			$cartItem->delete();
		}
		if (request()->wantsJson()) {
			return $this->returnCartContent();
		}
		return redirect()->back();
	}

	/**
	 * Empty cart
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
	 */
	public function destroy(): RedirectResponse|JsonResponse
	{
		$this->initializeCart();
		$this->cartItems->map->delete();
		Session::forget('coupon');
		Session::forget('qty');
		if (request()->wantsJson()) {
			return $this->returnCartContent();
		}
		return redirect()->back();
	}

}
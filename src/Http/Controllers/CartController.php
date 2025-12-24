<?php

namespace ApproTickets\Http\Controllers;

use ApproTickets\Models\Rate;
use ApproTickets\Models\Order;
use ApproTickets\Models\Product;
use ApproTickets\Models\Booking;
use ApproTickets\Models\ProductRate;
use ApproTickets\Models\Coupon;
use DB;
use Log;
use Illuminate\Http\JsonResponse;
use Session;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use ApproTickets\Traits\HandlesErrorResponse;
use ApproTickets\Http\Resources\CartItem;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Database\Eloquent\Collection;
use ApproTickets\Http\Controllers\PackController;
use ApproTickets\Http\Resources\Order as OrderResource;


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

	protected function returnCartContent($redirect = null): JsonResponse
	{
		$this->initializeCart();
		return response()->json([
			'items' => CartItem::collection($this->cartItems),
			'total' => $this->cartTotal,
			'redirect' => $redirect
		]);
	}

	/**
	 * Convert cart products to pack when eligible
	 * @param \ApproTickets\Models\Product $product
	 * @param array $rates
	 * @return void
	 */
	protected function convertToPack(Product $product, array $rates): bool
	{
		$this->initializeCart();

		$packCreated = false;

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

							$packCreated = true;

						}
					}
				}
			}
		}

		if ($packCreated) {
			session()->forget('pack');
		}

		return $packCreated;
	}

	/**
	 * Cart page (may be optional if cart is integrated in confirmation)
	 * @return \Illuminate\View\View
	 */
	public function show(): RedirectResponse|View
	{
		// Redirect to checkout in inertia project
		if (config('approtickets.inertia')) {
			return redirect()->route('checkout');
		}

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
		$qtys = $data['qty'] ?? [0];

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

		// Optimized price fetching
		$rateIds = array_filter($rates, fn($r) => !is_null($r));
		$prices = DB::table('product_rate')
			->where('product_id', $product_id)
			->whereIn('rate_id', $rateIds)
			->pluck('price', 'rate_id');

		foreach ($qtys as $i => $qty) {

			if ($qty <= 0)
				continue;

			$rate_id = $rates[$i] ?? null;
			if (!$rate_id || !isset($prices[$rate_id]))
				continue;

			$price = $prices[$rate_id];

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
			// Eager load relationships to prevent N+1 in convertToPack
			$product->load(['packs.rates', 'packs.packProducts']);
			$packsCreated = $this->convertToPack($product, $rates);
		}

		$redirect = null;
		if (isset($data['addToPack']) && !$packsCreated) {
			$pack = Product::find($data['addToPack']);
			$nextProduct = PackController::redirectToNextProduct($pack);
			$redirect = route('product', ['name' => $nextProduct], false);
		}

		if (request()->wantsJson()) {
			// if ($packsCreated) {
			// 	return $this->returnCartContent(route('checkout', [], false));
			// }
			return $this->returnCartContent($redirect);
		}

		if ($redirect) {
			return redirect($redirect);
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

		try {
			DB::transaction(function () use ($seats, $product, $rate_id, $day, $hour, $takenSeats) {

				// Lock the rows for update to prevent race conditions
				// Since we are creating new rows, we can't lock them directly.
				// We should lock the checking query.
				// However, standard select for update might not work if rows don't exist.
				// A common approach is to lock the product or a parent record, but that might be too aggressive.
				// Better approach for seat reservation:
				// 1. Check availability
				// 2. Insert
				// 3. If unique constraint fails, rollback (handled by DB)
				// But here we want to prevent overbooking.

				// Let's use lockForUpdate on the check query if possible, but it only locks existing rows.
				// To be safe against race conditions where two people book the same seat at the same time:
				// We should rely on a unique constraint in the DB (product_id, day, hour, seat, row).
				// Assuming the user will add that constraint or we handle the exception.
				// For now, let's wrap in transaction and do a 'lock' by selecting for update on the product maybe?
				// Or just use the transaction to ensure atomicity.

				// A better way without unique constraint (if we can't add it now) is to lock the product record
				// This serializes bookings for the same product, which is safe but might be slightly slower under high load.
				$product = Product::lockForUpdate()->find($product->id);

				$rate = Rate::find($rate_id);
				$productRate = ProductRate::where('product_id', $product->id)
					->where('rate_id', $rate->id)->first();
				$generalPrice = $productRate->price;

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
						// Throw exception to rollback or handle gracefully
						Log::error('Seat taken: ' . $seat->s . ' ' . $seat->f);
						throw new \Exception(__('approtickets::cart.taken_seats'));
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
			});

			// Error handling is now done via exception in transaction
		} catch (\Exception $e) {
			Log::error('Error adding seats to cart: ' . $e->getMessage());
			return $this->handleErrorResponse($e->getMessage());
		}

		if ($product->packs->count() > 0) {
			$this->convertToPack($product, [$rate_id]);
		}

		if (request()->wantsJson()) {
			return $this->returnCartContent();
		}

		return redirect()->back()->with('itemAdded', true);

	}

	/**
	 * Apply a coupon
	 * @return RedirectResponse
	 */
	public function applyCoupon(): RedirectResponse|JsonResponse
	{

		$code = request()->input('code');
		$coupons = Coupon::where('code', $code)
			->where('validity', '>', now())->get();

		if (count($coupons) > 0) {

			if (!Session::has('coupon')) {

				foreach ($coupons as $coupon) {

					$couponSessionId = "coupon.p{$coupon->product_id}_t{$coupon->rate_id}";
					if (!Session::has($couponSessionId)) {
						Session::put($couponSessionId, true);

						$rowsInCart = $coupon->product->inCart()
							->where('rate_id', $coupon->rate_id);

						foreach ($rowsInCart as $row) {
							$newPrice = $row->price * (1 - $coupon->discount / 100);
							$row->price = $newPrice;
							$row->save();
						}
					}

				}

				Session::put('coupon.name', $code);
				Session::put('coupon.discount', $coupon->discount);
				if (request()->wantsJson()) {
					return $this->returnCartContent();
				}
				return redirect()->route('cart')->with('message', 'Codi promocional correcte!');

			}

			if (request()->wantsJson()) {
				return $this->returnCartContent();
			}
			return redirect()->route('cart')->with('message', 'Ja has aplicat un descompte');

		}

		if (request()->wantsJson()) {
			return $this->returnCartContent();
		}
		return redirect()->route('cart')->with('message', 'El codi promocional no és vàlid.');

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
		$previousOrders = false;
		$loggedIn = false;
		if (auth()->check()) {
			$previousOrders = OrderResource::collection(auth()->user()->orders);
			// $previousOrders = !(auth()->user()->hasRole(['admin', 'organizer'])) ?
			// 	auth()->user()->orders : [];
			$loggedIn = (object) [
				'name' => auth()->user()->name,
				'email' => auth()->user()->email
			];
		}
		if (config('approtickets.inertia')) {
			return Inertia::render('Checkout', [
				'loggedIn' => $loggedIn,
				'previousOrders' => $previousOrders
			]);
		}
		return view('order.checkout', [
			'loggedIn' => $loggedIn,
			'previousOrders' => $previousOrders,
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
<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use ApproTickets\Models\Rate;
use ApproTickets\Models\Order;
use ApproTickets\Models\Product;
use DB;
use Gloudemans\Shoppingcart\Facades\Cart;
use Session;
use Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CartController extends BaseController
{


	protected $layout = 'cistell';


	/**
	 * Cart user page
	 */
	public function show(): View
	{
		Cart::instance('shopping')->setGlobalTax(0);
		return view('cart', [
			'cart' => Cart::content(),
			'total' => Cart::instance('shopping')->total()
		]);
	}

	public function apiCart()
	{
		return response()->json([
			'cart' => Cart::content()
		]);
	}

	/**
	 * Convert single products into packs
	 */
	public function convertToPack(Product $product): void
	{

		$packs = $product->packs;

		foreach ($packs as $pack) {

			$keys = $pack->packProducts->modelKeys();
			$rates = $pack->rates;

			if (!$rates)
				continue;

			foreach ($rates as $rate) {

				$preu = $rate->pivot->preu;
				$rows = Cart::search(function ($k, $v) use ($rate) {
					return $k->options->id_Rate == $rate->id;
				});

				$prods = array();
				$keysCart = array();

				if ($rows) {

					foreach ($rows as $row) {

						$rowid = $row->rowId;

						if (in_array($row->id, $keys) && !in_array($row->id, $keysCart)) {

							$prods['row'][] = $rowid;
							$prods['qty'][] = $row->qty;
							$prods['reserves'][] = array(
								'product' => $row->id,
								'titol' => $row->model->title,
								'localitat' => $row->options->seat,
								'day' => $row->options->dia,
								'hour' => $row->options->hora
							);
							$keysCart[] = $row->id;

						}

					}

					if (isset($prods["row"]) && (count($prods['row']) == count($keys))) {

						$min_entrades = min($prods['qty']);

						Cart::add(
							$pack->id,
							$pack->title,
							$min_entrades,
							$preu,
							0,
							array(
								'name' => $pack->name,
								'parent' => 0,
								'rate' => $rate,
								'target' => $pack->target,
								'reserves' => $prods['reserves']
							)
						)->associate('App\Models\Product');

						for ($i = 0; $i < count($prods['row']); $i++) {
							$qty = $prods['qty'][$i] - $min_entrades;
							if ($qty > 0) {
								Cart::update($prods['row'][$i], $qty);
							} else {
								Cart::remove($prods['row'][$i]);
							}

						}

					}

				}

			}

		}
	}


	/**
	 * Add standard item
	 */
	public function add(): RedirectResponse
	{

		$data = json_decode(request()->input('data'), true);

		$product_id = $data['product_id'] ?? null;
		$product = Product::find($product_id);
		if (!$product) {
			return redirect()->back();
		}

		$day = $data['day'] ?? null;
		$hour = $data['hour'] ?? null;
		$rates = $data['rates'] ?? null;
		$qtys = $data['qty'] ?? null;

		$seats = $data['seats'] ?? null;
		if ($seats) {
			$this->addEvent($seats, $product, $data['rate'], $day, $hour);
		}

		if (!$qtys) {
			return redirect()->back();
		}

		// Comprovar mímim d'entrades per a totes les tarifes
		$totalqtys = array_sum($qtys);
		if ($totalqtys < $product->min_tickets) {
			return response()->json([
				'error' => trans('textos.minimEntrades') . $product->min_tickets
			], 405);
		}

		// Màxim d'entrades
		$alCistell = Cart::search(function ($k, $v) use ($product_id, $day, $hour) {
			return $k->id == $product_id && $k->options->dia == $day && $k->options->hora == $hour;
		});
		$qtyCistell = 0;
		foreach ($alCistell as $prod) {
			$qtyCistell += $prod->qty;
		}
		if ($totalqtys + $qtyCistell > $product->max_tickets) {
			return response()->json([
				'error' => trans('textos.max_tickets', ['max' => $product->max_tickets])
			], 405);
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

			if (Session::has('coupon.p' . $product_id . '_t' . $rate_id)) {
				$price *= 1 - Session::get('coupon.discount') / 100;
			}

			try {

				Cart::instance('shopping')->add(
					$product->id,
					$product->title,
					$qty,
					$price,
					0,
					[
						'name' => $product->name,
						'product_id' => $product->id,
						'day' => $day,
						'hour' => $hour,
						'rate' => $rate->title,
						'rate_id' => $rate_id,
					]
				)->associate('\App\Models\Product');

			} catch (\Exception $e) {
				return response()->json([
					'error' => 'Hi ha hagut un error a l\'afegir el producte al cistell'
				], 500);
			}

		}

		$this->convertToPack($product);

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
		if(Session::has('coupon.p' . $product->id . '_t' . $rate->id)) {
			$price *= 1 - Session::get('coupon.discount') / 100;
		}
		
		foreach ($seats as $seat) {

			Cart::instance('shopping')->add(
				$product->id,
				$product->title,
				1,
				$price,
				0,
				[
					'name' => $product->name,
					'product_id' => $product->id,
					'day' => $day,
					'hour' => $hour,
					'rate' => $rate->title,
					'rate_id' => $rate->id,
					'seat' => ['s' => $seat['s'], 'f' => $seat['f']]
				]
			)->associate('\App\Models\Product');

		}

		$this->convertToPack($product);

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

		Cart::instance('shopping')->setGlobalTax(0);

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
	public function removeRow(): RedirectResponse
	{
		$rowid = request()->input('rowid');
		$cartitem = Cart::instance('shopping')->content()->where('rowId', $rowid);
		if ($cartitem->isNotEmpty()) {
			Cart::instance('shopping')->remove($rowid);
		}
		return redirect()->back();
	}


	/**
	 * Update cart row (not using)
	 */
	public function updateItem($rowId): RedirectResponse
	{
		$cartitem = Cart::instance('shopping')->content()->where('rowId', $rowId);
		if ($cartitem->isNotEmpty()) {
			Cart::instance('shopping')->remove($rowId);
		}
		return redirect()->route('cart');
	}


	/**
	 * Delete all cart rows
	 */
	public function destroy(): RedirectResponse
	{
		Cart::instance('shopping')->destroy();
		Session::forget('coupon');
		Session::forget('qty');
		return redirect()->back();

	}

}
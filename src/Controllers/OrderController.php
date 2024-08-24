<?php

namespace ApproTickets\Controllers;

use Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Gloudemans\Shoppingcart\Facades\Cart;
use Redsys\Tpv\Tpv;
use ApproTickets\Models\Order;
use ApproTickets\Models\User;
use ApproTickets\Models\Booking;
use ApproTickets\Models\Product;
use Session;
use ApproTickets\Helpers\Common;
use Mail;
use ApproTickets\Mail\NewOrder;

class OrderController extends Controller
{

	public function store(): RedirectResponse
	{

		$failedOrder = Order::where('session', Session::getId())
			->where('paid', 0)
			->orderBy('created_at', 'desc')
			->first();
		if ($failedOrder) {
			dd('ja hi ha una comanda pendent');
			return redirect()->route('order.payment', ['id' => $failedOrder->id]);
		}

		$cartItems = Booking::where('order_id', NULL)
			->where('session', Session::getId())
			->get();

		if (!$cartItems->count()) {
			return redirect()->route('home');
		}

		// Delete order with same session id when user goes back
		$orderError = Order::where('session', Session::getId())
			->where('paid', 0)
			->first();
		if ($orderError) {
			$orderError->delete();
		}

		$rules = !(auth()->check() && auth()->user()->hasRole('admin')) ? [
			'condicions' => 'accepted',
			'name' => 'required',
			'tel' => 'required',
			'email' => 'required|email',
			'cp' => 'required|size:5'
		] : [
			'condicions' => 'accepted',
			'name' => 'required',
			'email' => 'required|email',
		];

		$validator = validator(request()->all(), $rules);
		if ($validator->fails()) {
			dd('ha fallat la validació')
			return redirect()->back()->withErrors($validator)->withInput();
		}

		// Create new user if password is submitted
		// if (request()->has('password') && !empty(request()->input('password'))) {
		// 	$validatorU = validator(request()->all(), [
		// 		'password' => 'confirmed|min:6',
		// 		'email' => 'unique:users,email'
		// 	]);
		// 	if ($validatorU->fails()) {
		// 		return redirect()->back()->withErrors($validatorU)->withInput();
		// 	}
		// 	$user = new User;
		// 	$user->username = request()->input('name');
		// 	$user->email = request()->input('email');
		// 	$user->password = request()->input('password');
		// 	$user->save();
		// }


		// Check availabilities before checkout
		// foreach ($cartItems as $row) {

		// 	// Venue events
		// 	if ($row->seat) {
		// 		$booked = Booking::where('product_id', $row->model->id)
		// 			->where('day', $row->options->dia)
		// 			->where('hour', $row->options->hora)
		// 			->where('seat', json_encode($row->options->seat))
		// 			->whereHas('order', function ($query) {
		// 				$query->whereNull('deleted_at');
		// 			})
		// 			->first();
		// 		if ($booked) {
		// 			return redirect()->back()->withErrors('Ho sentim, la localitat <strong>' . Common::seat($row->options->seat) . '</strong> per a <strong>' . $row->model->title . '</strong> ja ha sigut adquirida per un altre usuari. Si us plau, esculli una altra localitat.')->withInput();
		// 		}
		// 	} else {
		// 		if ($row->model->is_pack) {
		// 			// TODO: Programar que per cada producte del pack comprovi si queden entrades disponibles.
		// 		} else {
		// 			$tickets_day = $row->model->ticketsDay($row->options->day, $row->options->hour);
		// 			if ($tickets_day->available < 0) {
		// 				return redirect()->back()->with('message', 'Ho sentim, ja no hi ha entrades disponibles per al producte ' . $row->model->title . '. Redueixi la quantitat d\'entrades o canvii l\'hora o el dia de la visita.')->withInput();
		// 			}
		// 		}
		// 	}

		// }

		$total = $cartItems->sum(function ($item) {
            return $item->price;
        });

		$payment = ($total == 0 || (auth()->check() && auth()->user()->hasRole('admin'))) ? 'card' : 'card';

		request()->merge([
			'language' => 'ca',
			'session' => Session::getId(),
			'total' => $total,
			'coupon' => Session::get('coupon.name'),
			'payment' => $payment,
			'paid' => $payment == 'card' ? 0 : 1,
			'user_id' => $user->id ?? null
		]);

		$values = request()->except(['conditions', 'password', 'password_confirmation']);
		$order = Order::create($values);

		foreach ($cartItems as $row) {
			$row->order_id = $order->id;
			$row->uniqid = substr(bin2hex(random_bytes(20)), -5);
			$row->save();
		}

		if ($order) {

			if ($order->payment == 'credit') {
				try {
					Mail::to($order->email)->send(new NewOrder($order));
				} catch (\Exception $e) {
					Log::error($e->getMessage());
				}
				Session::forget('coupon');
				Session::forget('coupon_name');
				return view('order.thanks')->with('order', $order);
			}

			if (config('app.env') != 'production') {
				$order->paid = 1;
				$order->save();
			}

			return redirect()->route('order.payment', ['id' => $order->id]);

		}

		return redirect()->back()->withErrors('Error al processar la comanda. Si us plau, torni a intentar-ho.');

	}
}

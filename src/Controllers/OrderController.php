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


    public static function cleanNonProcessed(): void
	{
		$date = new \DateTime;
		$date->modify('-60 minutes');
		$formatted = $date->format('Y-m-d H:i:s');
		Order::where('paid', '!=', 1)
			->where('payment', 'card')
			->where('created_at', '<=', $formatted)
			->delete();
		return;
	}

    public function store(): RedirectResponse
	{

		$failedOrder = Order::where('session', Session::getId())
			->where('paid', 0)
			->orderBy('created_at', 'desc')
			->first();
		if ($failedOrder) {
			return redirect()->route('order.payment', ['id' => $failedOrder->id]);
		}

		$this->cleanNonProcessed();

		if (!Cart::instance('shopping')->count()) {
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
			return redirect()->back()->withErrors($validator)->withInput();
		}

		// Create new user if password is submitted
		if (request()->has('password') && !empty(request()->input('password'))) {
			$validatorU = validator(request()->all(), [
				'password' => 'confirmed|min:6',
				'email' => 'unique:users,email'
			]);
			if ($validatorU->fails()) {
				return redirect()->back()->withErrors($validatorU)->withInput();
			}
			$user = new User;
			$user->username = request()->input('name');
			$user->email = request()->input('email');
			$user->password = request()->input('password');
			$user->save();
		}


		// Check availabilities before checkout
		foreach (Cart::content() as $row) {

			// Venue events
			if ($row->options->seat) {
				$booked = Booking::where('product_id', $row->model->id)
					->where('day', $row->options->dia)
					->where('hour', $row->options->hora)
					->where('seat', json_encode($row->options->seat))
					->whereHas('order', function ($query) {
						$query->whereNull('deleted_at');
					})
					->first();
				if ($booked) {
					return redirect()->back()->withErrors('Ho sentim, la localitat <strong>' . Common::seat($row->options->seat) . '</strong> per a <strong>' . $row->model->title . '</strong> ja ha sigut adquirida per un altre usuari. Si us plau, esculli una altra localitat.')->withInput();
				}
			} else {
				if ($row->model->is_pack) {
					// TODO: Programar que per cada producte del pack comprovi si queden entrades disponibles.
				} else {
					$tickets_day = $row->model->ticketsDay($row->options->day, $row->options->hour);
					if ($tickets_day->available < 0) {
						return redirect()->back()->with('message', 'Ho sentim, ja no hi ha entrades disponibles per al producte ' . $row->model->title . '. Redueixi la quantitat d\'entrades o canvii l\'hora o el dia de la visita.')->withInput();
					}
				}
			}

		}

		$total = Cart::instance('shopping')->total();
		$payment = ( $total == 0 || (auth()->check() && auth()->user()->hasRole('admin')) ) ? 'card' : 'card';

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

		foreach (Cart::content() as $row) {

			// Reserves dels packs
			if ($row->model->is_pack) {

				$booking = new Booking;
				$booking->tickets = $row->qty;
				$booking->price = $row->price;
				$booking->is_pack = 1;
				$booking->product()->associate($row->model);
				$booking->rate()->associate($row->options->rate_id);
				$booking->order()->associate($order);

				$isr = true;

				foreach ($row->options->bookings as $subreserva) {

					$subproducte = Product::find($subreserva["producte"]);

					if ($isr == true) {
						$booking->day = $subreserva["day"];
						$booking->hour = $subreserva["hour"];
						$booking->save();
						$isr = false;
					}

					$sreserva = new Booking;
					$sreserva->day = $subreserva["day"];
					$sreserva->hour = $subreserva["hour"];
					$sreserva->tickets = $row->qty;
					$sreserva->price = 0;
					$sreserva->uniqid = substr(bin2hex(random_bytes(20)), -5);
					$sreserva->product()->associate($subproducte);
					$sreserva->rate()->associate($row->options->rate_id);
					$sreserva->order()->associate($order);
					$sreserva->save();

				}

			} else {

				$booking = new Booking;
				$booking->day = $row->options->day;
				$booking->hour = $row->options->hour;
				$booking->tickets = $row->qty;
				$booking->price = $row->price;
				$booking->uniqid = substr(bin2hex(random_bytes(20)), -5);
				if ($row->options->seat) {
					$booking->seat = json_encode($row->options->seat);
				}
				$booking->product()->associate($row->model);
				$booking->rate()->associate($row->options->rate_id);
				$booking->order()->associate($order);
				$booking->save();

			}

		}

		if ($order) {

			if ($order->payment == 'credit') {
				try {
					Mail::to($order->email)->send(new NewOrder($order));
				} catch (\Exception $e) {
					Log::error($e->getMessage());
				}
				Cart::destroy();
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

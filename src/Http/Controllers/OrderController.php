<?php

namespace ApproTickets\Http\Controllers;

use Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use ApproTickets\Models\Order;
use ApproTickets\Models\Booking;
use ApproTickets\Models\User;
use Session;
use Mail;
use ApproTickets\Mail\NewOrder;
use Redsys\Tpv\Tpv;
use Barryvdh\DomPDF\Facade\Pdf;
use ApproTickets\Models\Option;
use Illuminate\Routing\Controller as BaseController;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use ApproTickets\Enums\PaymentStatus;
use ApproTickets\Enums\PaymentMethods;

class OrderController extends BaseController
{

	public function store(): RedirectResponse|array
	{

		$failedOrder = Order::where('session', Session::getId())
			->where('paid', 0)
			->orderBy('created_at', 'desc')
			->first();
		if ($failedOrder) {
			return redirect()->route('order.payment', ['id' => $failedOrder->id]);
		}

		$cartItems = Booking::where('order_id', NULL)
			->where('session', Session::getId())
			->get();

		$isOrganizer = false;

		if (auth()->check()) {
			$user = auth()->user();

			if ($user->hasRole('admin')) {
				$isOrganizer = true;
			} elseif ($user->hasRole('organizer')) {
				$productOrganizers = $cartItems->pluck('product.user_id')->unique();

				if ($productOrganizers->count() > 1) {
					return redirect()->back()->withErrors([
						'generalError' => __("Només pots reservar entrades per l'organitzador ':username'. Revisa el teu cistell.", ['username' => $user->name])
					])->withInput();
				}

				$isOrganizer = true;
			}
		}

		if (!$cartItems->count()) {
			return redirect()->route('home');
		}

		$rules = !auth()->check() ? [
			'conditions' => 'accepted',
			'name' => 'required',
			'phone' => 'required',
			'email' => 'required|email'
		] : [
			'conditions' => 'accepted',
			'name' => 'required',
			'email' => 'required|email',
		];

		$validator = validator(request()->all(), $rules);
		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->withInput();
		}

		$user = auth()->check() ? auth()->user() : null;

		if (!empty(request()->input('password'))) {
			$newUserValidator = validator(request()->all(), [
				'password' => 'confirmed|min:6',
				'email' => 'unique:users,email'
			]);
			if ($newUserValidator->fails()) {
				return redirect()->back()->withErrors($newUserValidator)->withInput();
			}
			$user = new User;
			$user->name = request()->input('name');
			$user->email = request()->input('email');
			$user->password = request()->input('password');
			$user->save();
			auth()->login($user);
		}

		$total = $cartItems->sum(fn($item) => $item->price * $item->tickets);

		$payment = $isOrganizer ? 'credit' : request()->input('payment', 'card');
		$paid = $payment == 'card' ? 0 : 1;
		if ($total == 0) {
			$paid = 1;
		}

		$order = Order::create([
			'lang' => app()->getLocale(),
			'session' => Session::getId(),
			'total' => $total,
			'coupon' => Session::get('coupon.name'),
			'payment' => $payment,
			'paid' => $paid,
			'user_id' => $user->id ?? null,
			'name' => request()->input('name'),
			'email' => request()->input('email') ?? $user->email,
			'phone' => request()->input('phone'),
			'cp' => request()->input('cp'),
			'observations' => request()->input('observations'),
		]);

		//$organizers = [];

		foreach ($cartItems as $booking) {
			$booking->order_id = $order->id;
			$booking->uid = substr(bin2hex(random_bytes(20)), -5);
			$booking->save();
			//$organizers[] = $booking->product->organizer_id;
		}

		if ($order) {

			if ($order->paid == PaymentStatus::PAID) {
				try {
					Mail::to($order->email)->send(new NewOrder($order));
				} catch (\Exception $e) {
					Log::error($e->getMessage());
				}
				return redirect()->route('order.thanks', ['session' => $order->session, 'id' => $order->id]);
			}

			return redirect()->route('order.payment', ['id' => $order->id]);

		}

		return redirect()->back()->withErrors('Error al processar la comanda. Si us plau, torni a intentar-ho.');

	}

	public function payment($id)
	{
		$order = Order::findOrFail($id);
		if ($order->paid == PaymentStatus::PAID) {
			return redirect()->route('order.thanks', ['session' => $order->session, 'id' => $order->id]);
		}

		$uniqid = str_pad(mt_rand(0, 99), 2, '0', STR_PAD_LEFT);

		$TPV = new Tpv(config('redsys'));
		$appName = config('app.name');
		$TPV->setFormHiddens(
			[
				'TransactionType' => '0',
				'MerchantData' => "Comanda {$appName} {$order->id}",
				'MerchantURL' => route('tpv-notification'),
				'Order' => "{$order->id}{$uniqid}1",
				'Amount' => $order->total,
				'UrlOK' => route('order.thanks', ['session' => $order->session, 'id' => $order->id]),
				'UrlKO' => route('order.error', ['session' => $order->session, 'id' => $order->id])
			]
		);

		return view('order.tpv', [
			'TPV' => $TPV
		]);
	}

	public function thanks(string $session, string $id): RedirectResponse|View|InertiaResponse
	{
		$order = Order::where('session', $session)
			->where('id', $id)
			->isPaid()
			->orderBy('created_at', 'desc')
			->firstOrFail();
		Session::forget('coupon');
		Session::forget('coupon_name');
		if (config('approtickets.inertia')) {
			$download = route('order.pdf', ['session' => $order->session, 'id' => $order->id]);
			$title = $order->payment == PaymentMethods::Card ?
				__('Gràcies per la teva compra') :
				__('Entrades reservades');
			return Inertia::render('order/Thanks', [
				'title' => $title,
				'download' => $download
			]);
		}
		return view('order.thanks')->with('order', $order);

	}

	/**
	 * Error page
	 * @param string $session
	 * @param string $id
	 * @return \Illuminate\View\View|\Inertia\Response
	 */
	public function error(string $session, string $id): View|InertiaResponse|RedirectResponse
	{
		if (!$session == Session::getId()) {
			return redirect()->route('home');
		}
		$order = Order::where('session', Session::getId())
			->where('id', $id)
			->where('paid', '!=', 1)
			->orderBy('created_at', 'desc')
			->firstOrFail();

		if (config('approtickets.inertia')) {
			return Inertia::render('order/Error', [
				'title' => __('Error en el pagament'),
				'payment' => route('order.payment', ['id' => $order->id]),
				'limit' => $order->created_at->addHour()->format('H:i')
			]);
		}
		return view('order.error')->with('order', $order);

	}

	/**
	 * Generate order PDF with tickets
	 */
	public function pdf(string $session, string $id)
	{
		$order = Order::findOrFail($id);

		if ($order->session != $session || $order->paid != PaymentStatus::PAID) {
			return abort(404);
		}

		$conditions = Option::text('order-conditions');

		// $pdfPath = storage_path("app/tickets/entrades-{$id}.pdf");
		// if (file_exists($pdfPath) && !auth()->check()) {
		// 	return response()->file($pdfPath);
		// }

		// $pdf = Pdf::setOptions(['isRemoteEnabled' => true])->loadView(
		// 	'pdf.order',
		// 	[
		// 		'order' => $order,
		// 		'conditions' => $conditions
		// 	]
		// );

		// $pdf->save($pdfPath);

		return view('pdf.order', [
			'order' => $order,
			'conditions' => $conditions
		]);

		//return $pdf->stream("entrades-{$id}.pdf");

	}

	public function previousOrders(): View|InertiaResponse
	{
		$orders = Order::where('user_id', auth()->user()->id)
			->where('paid', 1)
			->orderBy('created_at', 'desc')
			->get();

		if (config('approtickets.inertia')) {
			return Inertia::render('order/PreviousOrders', [
				'title' => __('Les teves comandes anteriors'),
				'orders' => $orders
			]);
		}
		return view('order.previous')->with('orders', $orders);
	}

}

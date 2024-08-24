<?php

namespace ApproTickets\Controllers;

use Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use ApproTickets\Models\Order;
use ApproTickets\Models\Booking;
use Session;
use Mail;
use ApproTickets\Mail\NewOrder;
use Redsys\Tpv\Tpv;
use Barryvdh\DomPDF\Facade\Pdf;
use ApproTickets\Models\Option;
use Illuminate\Routing\Controller as BaseController;

class OrderController extends BaseController
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
			'phone' => 'required',
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

		$total = $cartItems->sum(function ($item) {
			return $item->price;
		});

		$order = Order::create([
			//'lang' => 'ca',
			'session' => Session::getId(),
			'total' => $total,
			'coupon' => Session::get('coupon.name'),
			'payment' => request()->input('payment'),
			'paid' => request()->input('payment') == 'card' ? 0 : 1,
			'user_id' => $user->id ?? null,
			'name' => request()->input('name'),
			'email' => request()->input('email'),
			'phone' => request()->input('phone'),
			'cp' => request()->input('cp'),
			'observations' => request()->input('observations'),
		]);

		foreach ($cartItems as $booking) {
			$booking->order_id = $order->id;
			$booking->uniqid = substr(bin2hex(random_bytes(20)), -5);
			$booking->save();
		}

		if ($order) {

			if ($order->paid) {
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
		if ($order->paid == 1) {
			return redirect()->route('order.thanks', ['session' => $order->session, 'id' => $order->id]);
		}

		$uniqid = str_pad(mt_rand(0, 99), 2, '0', STR_PAD_LEFT);

		$TPV = new Tpv(config('redsys'));
		$appName = config('app.name');
		$TPV->setFormHiddens(
			[
				'TransactionType' => '0',
				'MerchantData' => "Comanda {$appName} {$order->id}",
				'MerchantURL' => config('app.url') . '/tpv-notification',
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

	public function thanks(string $session, string $id): RedirectResponse|View
	{
		if (!$session == Session::getId()) {
			return redirect()->route('home');
		}
		$order = Order::where('session', Session::getId())
			->where('id', $id)
			->isPaid()
			->orderBy('created_at', 'desc')
			->firstOrFail();

		Session::forget('coupon');
		Session::forget('coupon_name');
		return view('order.thanks')->with('order', $order);

	}

	public function error(string $session, string $id): View
	{

		$order = Order::where('session', Session::getId())
			->orderBy('created_at', 'desc')->where('paid', '!=', 1)
			->firstOrFail();

		return view('order.error')->with('order', $order);

	}

	/**
	 * Generate order PDF with tickets
	 */
	public function pdf(string $session, string $id)
	{
		$order = Order::findOrFail($id);

		if ($order->session != $session || !($order->paid == 1 || $order->payment == 'credit')) {
			return abort('404');
		}

		$conditions = Option::where('key', 'condicions-venda')->pluck('value')->first();

		$pdf = Pdf::setOptions(['isRemoteEnabled' => true])->loadView(
			'pdf.order',
			[
				'order' => $order,
				'conditions' => $conditions
			]
		);

		return $pdf->stream("entrades-{$id}.pdf");

	}

}

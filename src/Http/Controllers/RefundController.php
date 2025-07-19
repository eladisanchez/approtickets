<?php

namespace ApproTickets\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use ApproTickets\Models\Refund;
use Log;
use Illuminate\View\View;
use Mail;
use ApproTickets\Mail\RefundAlertMail;
use Redsys\Tpv\Tpv;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Http;

class RefundController extends BaseController
{

	/**
	 * Connects to Redsys and sends the refund request
	 * @param string $environmentUrl
	 * @param string $dsMerchantParameters
	 * @param string $dsSignature
	 * @param string $dsSignatureVersion
	 * @return array
	 */
	private static function sendRefundRequest(
		string $environmentUrl,
		string $dsMerchantParameters,
		string $dsSignature,
		string $dsSignatureVersion
	): array {
		$response = Http::asForm()->post($environmentUrl, [
			'Ds_MerchantParameters' => $dsMerchantParameters,
			'Ds_Signature' => $dsSignature,
			'Ds_SignatureVersion' => $dsSignatureVersion
		]);
		if ($response->successful()) {
			$jsonResponse = $response->json();
			if (isset($jsonResponse['errorCode'])) {
				if ($jsonResponse['errorCode'] == 'SIS0054') {
					return [
						'error' => "La comanda no existeix.",
					];
				}
				return [
					'error' => $jsonResponse['errorCode'],
				];
			}
			return [
				'success' => true
			];
		} else {
			Log::error('Error en la petició a Redsys: ' . $response->body());
			return [
				'error' => "S'ha produït un error en la connexió amb Redsys.",
				'status' => $response->status(),
			];
		}
	}

	/**
	 * Gets the redsys refund request parameters
	 * @param \ApproTickets\Models\Refund $refund
	 * @return array
	 */
	public static function requestRefund(Refund $refund): array
	{
		if ($refund->refunded_at) {
			return [
				'error' => 'Aquesta devolució ja ha estat executada'
			];
		}
		$tpv = new Tpv(config('redsys'));
		$appName = config('app.name');
		$tpv->setFormHiddens(
			[
				'TransactionType' => '3',
				'MerchantData' => "{$appName} Devolució {$refund->order->tpv_id}",
				'MerchantURL' => config('app.url') . '/refund-notification',
				'Order' => $refund->order->tpv_id,
				'Amount' => $refund->total
			]
		);
		$formValues = $tpv->getFormValues();
		$response = self::sendRefundRequest(
			$tpv->getPath('/rest/trataPeticionREST'),
			$formValues['Ds_MerchantParameters'],
			$formValues['Ds_Signature'],
			$formValues['Ds_SignatureVersion']
		);
		if (isset($response['error'])) {
			return $response;
		}
		return [
			'success' => true
		];
	}

	/**
	 * Gets the refund query page
	 * @param string $hash
	 * @return \Illuminate\View\View|\Inertia\Response
	 */
	public function show(string $hash): View|InertiaResponse
	{
		$refund = Refund::where('hash', $hash)
			->whereNull('refunded_at')
			->with('order')
			->where('created_at', '>', now()->subMonths(1))
			->firstOrFail();

		$tpv = new Tpv(config('redsys'));
		$tpv->setFormHiddens(
			[
				'TransactionType' => '3',
				'MerchantData' => "Devolució comanda {$refund->order_id}",
				'MerchantURL' => route('refund-notification'),
				'Order' => $refund->order->tpv_id,
				'Amount' => $refund->total,
				'UrlOK' => route('refund', ['hash' => $refund->hash]),
				'UrlKO' => route('refund', ['hash' => $refund->hash])
			]
		);

		if (config('approtickets.inertia')) {
			$content = view('approtickets::partials.refund', [
				'refund' => $refund,
				'tpv' => $tpv
			])->render();
			return Inertia::render('Basic', [
				'title' => __('Devolució'),
				'content' => $content
			]);
		}
		return view('checkout.refund', [
			'refund' => $refund,
			'tpv' => $tpv
		]);
	}

	/**
	 * TPV refund notification
	 * @return void
	 */
	public function notification(): void
	{

		$TPV = new Tpv(config('redsys'));

		try {

			$data = $TPV->checkTransaction($_POST);
			Log::debug('Redsys notification', $data);
			if (!$data['Ds_Order']) {
				return;
			}
			if ($data["Ds_Response"] == 900) {
				$order_id = substr($data["Ds_Order"], 0, -3);
				$refund = Refund::where('order_id', $order_id)->first();
				if ($refund) {
					$refund->update([
						'refunded_at' => now()
					]);
					Mail::to(config('mail.from.address'))->send(new RefundAlertMail($refund));
					Log::debug("Devolució efectuada de la comanda {$order_id}");
				} else {
					Log::error("Devolució sense comanda associada");
				}
			} else {
				Log::error("Error en la devolució: {$data['Ds_Response']}", $data);
			}

		} catch (\Exception $e) {
			$data = $TPV->getTransactionParameters($_POST);
			Log::error('Error en la resposta del TPV: ' . $e->getMessage(), $data);
		}

		return;

	}

}

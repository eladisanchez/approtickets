<?php 

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use ApproTickets\Models\Refund;
use Log;
use Illuminate\View\View;
use Mail;

class RefundController extends BaseController {


    public function index(): View
	{
        $devolucions = Refund::paginate(30);
        return view('admin.comandes.devolucions',['devolucions'=>$devolucions]);
    }

    public function refund(string $hash): View
	{
		$devolucio = Refund::where('hash',$hash)->firstOrFail();
		return view('checkout.refund',['devolucio'=>$devolucio]);
	}

    public function tpvResponse(): void
    {

        try {

			if(!$_POST) {
				$_POST = $_GET;
			}

			$redsys = new \Sermepa\Tpv\Tpv();

			$key = config('redsys.key');
			$parameters = $redsys->getMerchantParameters($_POST["Ds_MerchantParameters"]);
			$DsResponse = $parameters["Ds_Response"];
			$DsResponse += 0;

			if ($redsys->check($key, $_POST) && $DsResponse <= 99) {

				$idcomanda = substr($parameters["Ds_Order"], 0, -3);
				$devolucio = Refund::where('comanda_id',$idcomanda)->first();

				if ($devolucio) {

					// Missatge OK
					$devolucio->update(array(
						'retornat'=>1
					));

					// Email per Administrador
					Mail::send('emails.refund-notice', ['refund'=>$devolucio], function($message)
					{
						$message->from(config('mail.from.address'), config('mail.from.name'));
						$message->to(config('mail.from.address'));
						$message->subject('Devolució efectuada');
					});

                    Log::debug('Devolució efectuada de la comanda '.$idcomanda);
						
				}

			} else {

				Log::error("Error al fer la devolució");

			}

		} catch (\Exception $e) {

			echo 'Caught exception: ',  $e->getMessage(), "\n";
			Log::error($e->getMessage());

		}

        return;

    }
	

}
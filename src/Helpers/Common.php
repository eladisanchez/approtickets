<?php
namespace ApproTickets\Helpers;

use DB;

class Common
{

    public static function seat($seient)
    {

        if (!is_object($seient)) {
            if (strlen($seient) < 5) {
                return __('Fila') . ' ' . substr($seient, 0, -2) . ' ' . __('Seient') . ' ' . (int) substr($seient, -2);
            }
            $seient = json_decode($seient);
        }
        if (empty($seient->f) || $seient->f == 0) {
            return __('Localitat') . ' ' . $seient->s;
        }
        return __('Fila') . ' ' . $seient->f . ' ' . __('Seient') . ' ' . $seient->s;

    }

    public static function seatSmall($seient)
    {
        if ($seient->f == 0) {
            return __('Localitat') . ' ' . $seient->s;
        }
        return 'F' . $seient->f . '/S' . $seient->s;
    }

}
<?php

namespace ApproTickets\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use ApproTickets\Models\Booking;
use ApproTickets\Models\Scan;
use ApproTickets\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QrController extends BaseController
{

    /** 
     * App login
     */
    public function login(Request $request): JsonResponse
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credencials incorrectes'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->name
        ]);

    }


    /**
     * Test app with fake qr
     */
    public function testQr(Request $request): JsonResponse
    {
        $codi = $request->input('qr');

        if ($codi == '5sZqAR9QxjhyG8d=') {

            return response()->json([
                'message' => 'El codi no és correcte',
                'codi' => $codi
            ], 403);

        } else if ($codi == 'UwY8J8nXBQyDbza=') {

            return response()->json([
                'message' => 'Codi correcte',
                'codi' => $codi
            ], 200);

        }

        return response()->json([
            'message' => 'El codi no és vàlid',
            'codi' => $request->input('qr')
        ], 403);

    }

    public function checkQr(Request $request): JsonResponse
    {

        $code = explode('_', base64_decode($request->input('qr')));

        // Code not well formed (3 parts)
        if (count($code) < 3) {
            return response()->json([
                'message' => 'El codi no és vàlid',
                'codi' => $request->input('qr')
            ], 403);
        }

        $uniqid = $code[1];
        $count = $code[2];
        $booking_id = $code[3] ?? false;

        $booking = !$booking_id ?
            Booking::where('uid', $uniqid)->orderBy('created_at', 'desc')->first() :
            Booking::where('uid', $uniqid)->where('id', $booking_id)->first();

        // Booking does not exist or ticket number is greater to booking tickets
        if (!$booking || $count > $booking->tickets):

            return response()->json([
                'message' => __('El codi no és correcte'),
                'codi' => $code
            ], 403);

        endif;

        // User can scan this code
        // $isentitat = $request->user()->productes->contains($booking->product_id);
        $is_entity = $request->user()->hasRole('organizer');
        $is_admin = $request->user()->hasRole('admin');
        $is_validator = $request->user()->hasRole('validator');

        if (!$is_entity && !$is_admin && !$is_validator) {
            return response()->json(
                [
                    'message' => __("El codi no correspon a l'esdeveniment"),
                    'codi' => $code
                ],
                403
            );
        }

        // Comprovar temps 
        $to_time = strtotime($booking->day->format('Y-m-d') . ' ' . $booking->hour . ':00');
        $from_time = strtotime('now');
        $diff = round(($to_time - $from_time) / 60);
        // Minuts previs a l'inici de la sessió a partir dels que es podrà llegir l'entrada
        $minutesLeft = $booking->product->validation_start ?? 60;
        // Caducitat de l'entrada a partir de l'inici de sessió
        $minutesPast = $booking->product->validation_end ?? 60;

        // Esdeveniment futur
        if ($diff > $minutesLeft):
            $units = 'minuts';
            if ($diff / 60 > 1) {
                $units = 'hores';
                $diff = round($diff / 60);
                if ($diff / 24 > 1) {
                    $units = 'dies';
                    $diff = round($diff / 24);
                }
            }
            return response()->json([
                'message' => "Falten {$diff} {$units} per l'espectacle",
                'codi' => $code
            ], 403);
        endif;

        // Entrada caducada
        if ($diff < -$minutesPast):
            return response()->json(
                [
                    'message' => "Aquest codi ja no és vàlid",
                    'codi' => $code
                ],
                403
            );
        endif;

        // QR already scanned
        $scan = Scan::where('booking_id', $booking->id)
            ->where('scan_id', $count)
            ->first();

        if ($scan):
            return response()->json(
                [
                    'message' => __('Aquest codi ja s\'ha utilitzat'),
                    'codi' => $code
                ],
                403
            );
        endif;

        // Save scan
        $booking->scans()->create([
            'scan_id' => $count
        ]);
        $booking->load('scans');
        return response()->json(
            [
                'message' => __('Codi correcte'),
                'product' => $booking->product,
                'codi' => $code
            ],
            200
        );

    }

}
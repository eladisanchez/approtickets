<?php

namespace ApproTickets\Http\Controllers;

use Illuminate\Http\Request;
use ApproTickets\Models\User;
use ApproTickets\Models\Order;
use ApproTickets\Models\Booking;

class UserController extends Controller
{

    public function loginForm()
    {
        return redirect()->route('filament.admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (auth()->attempt($request->only('email', 'password'))) {
            return redirect()->route('checkout');
        }
        return redirect()->back()->with('error', 'El correu o la contrassenya són incorrectes');
    }

    public function logout()
    {
        auth()->logout();
        return redirect()->route('checkout');
    }
}
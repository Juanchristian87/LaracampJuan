<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Checkout;
use Auth;

class HomeController extends Controller
{
    public function dashboard()
    {
        $checkouts = Checkout::with('Camp')->whereUserId((Auth::id()))->get();// ini untuk ambil nama user yang checkout dari id user yang login
        return view ('user.dashboard', [
            'checkouts' => $checkouts
        ]);
    }
}

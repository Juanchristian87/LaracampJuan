<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Checkout;
use Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $checkouts = Checkout::with('Camp')->get(); // ini untuk ambil nama user yang checkout dari id user yang login
        return view('admin.dashboard', [
            'checkouts' => $checkouts
        ]);
    }
}

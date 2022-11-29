<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Auth;
use Mail;
use App\Mail\User\AfterRegister;

class UserController extends Controller
{
    public function login()
    {
        return view('auth.user.login');
    }

    public function google()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleProviderCallback()
    {
        $callback = Socialite::driver('google')->stateless()->user();/*ini untuk mengambil data dari email yang telah kita gunakan untuk login*/
        $data = 
        [
            'name' => $callback ->getName(),
            'email' => $callback ->getEmail(),
            'avatar' => $callback ->getAvatar(),
            'email_verified_At' =>date('Y-m-d H:i:s', time()),
        ];
        /*ini data yang diambil*/
        // $user = User::firstOrCreate(['email' => $data['email']], $data); /*ini untuk create email atau user baru yang terdaftar selain email default*/
        $user = User::whereEmail($data['email'])->first();/*cek datanya ada atau enggak*/
        if(!$user) /*apabila gak ada atau bisa dibilang emailnya belum terdaftar*/
        {
            $user = User::create($data);
            Mail::to($user->email)->send(new AfterRegister($user));/*kemudian akan dilempar ke user*/
        }

        Auth::login($user, true);

        return redirect(route('welcome')); /*setelah login langsung balik ke welcome page*/
        /*ini data yang diambil*/
    }
}

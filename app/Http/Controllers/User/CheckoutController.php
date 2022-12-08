<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use Illuminate\Http\Request;
use App\Http\Requests\User\Checkout\Store;
use App\Models\Camp;
use App\Mail\Checkout\AfterCheckout;
use Auth;
use Exception;
use Mail;
use Str;
use Midtrans;

class CheckoutController extends Controller
{
    //mendefinisikan variabel midtrans
    public function __construct()
    {
        Midtrans\Config::$serverkey = env('MIDTRANS_SERVERKEY');
        Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        Midtrans\Config::$isSanitized = env('MIDTRANS_IS_SANITIZED');
        Midtrans\Config::$is3DS = env('MIDTRANS_IS_3DS');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Camp $camp, Request $request) 
    {
        //condition untuk cek apakah kita sudah mengambil course tersebut apa belum
        if($camp->isRegistered)
        {
            $request->session()->flash('error', "You already registered on {$camp->title} camp." );
            return redirect(route('user.dashboard'));
        }
        return view('checkout.create', [
            'camp' => $camp
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Store $request, Camp $camp)
    {
        // return $request->all();//ini agar tidak di migrate ke database dulu
        //mapping request data
        $data = $request->all();
        $data['user_id'] = Auth::id();//mengambil id dari yang login
        $data['camp_id'] = $camp->id;//mengambil camp_id dari parameter

        //update user data di tabel
        $user = Auth::user();
        $user->email = $data['email'];
        $user->name = $data['name'];
        $user->occupation = $data['occupation'];
        $user->save();

        //Create Checkout
        $checkout = Checkout::create($data);
        //memanggil function baru 
        $this->getSnapRedirect($checkout);

        //sending Mail
        Mail::to(Auth::user()->email)->send(new AfterCheckout($checkout));//apabila user sign out maka akan dikirimkan email ke email user tersebut
        
        return redirect(route('checkout.success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Checkout  $checkout
     * @return \Illuminate\Http\Response
     */
    public function show(Checkout $checkout)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Checkout  $checkout
     * @return \Illuminate\Http\Response
     */
    public function edit(Checkout $checkout)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Checkout  $checkout
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Checkout $checkout)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Checkout  $checkout
     * @return \Illuminate\Http\Response
     */
    public function destroy(Checkout $checkout)
    {
        //
    }

    public function success()
    {
        return view ('checkout.success');
    }

    public function getSnapRedirect(Checkout $checkout)
    {
        $orderId = $checkout->id.'-'.Str::random(5);/*untuk meregenerate order_id*/
        $price = $checkout->Camp->price*1000;/* dikali 1000 karena harga di database masih dalam ratusan*/

        $checkout->midtrans_booking_code = $orderId;

        $transaction_details = [
            'order-id' => $orderId,
            'gross_amount' => $price,
        ];

        $item_details[] = [
            'id' => $orderId,
            'price' => $price,
            'quantity'=> 1,
            'name' => 'Payment for {$checkout->Camp->title} Camp',
        ];

        $userData = [
            "first_name" => $checkout -> User-> name,
            "last_name" => "",
            "aaddress" => $checkout->User->address,
            "city" => "",
            "postal_code" => "",
            "photo" => $checkout->User->phone,
            "country_code" => "IDN",
        ];

        $customer_details = [
            "first_name" => $checkout->User->name,
            "last_name" => "",
            "email" => $checkout->User->email,
            "phone" => $checkout->User->phone,
            "billing_address" => $userData,
            "shipping_address" => $userData,
        ];

        //parameter
        $midtrans_params = [
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $item_details,
        ];

        try{
            //Get Snap Payment Page URL
            $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;
            //kalau ddapat update tabel checkout yang midtrans URLnya
            $checkout->midtrans_url = $paymentUrl;
            $checkout->save();

            return $paymentUrl;
        } catch (Exception $e) {
            return false;
        }

    }

    // public function invoice(Checkout $checkout)
    // {
    //     return $checkout;
    // }//untuk return data checkout mengenai program apa yang dibeli
}

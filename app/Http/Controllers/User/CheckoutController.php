<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use Illuminate\Http\Request;
use App\Http\Requests\User\Checkout\Store;
use App\Models\Camp;
use App\Models\Discount;
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
        Midtrans\Config::$serverKey = "SB-Mid-server-YzQPLr_9UNT2GAgu9WUrAjp7";
        Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        Midtrans\Config::$isSanitized = env('MIDTRANS_IS_SANITIZED');
        Midtrans\Config::$is3ds = env('MIDTRANS_IS_3DS');
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
        $user->phone = $data['phone'];
        $user->address = $data['address'];
        $user->save();

        //check if there is discount
        if($request->discount)
        {
            $discount=Discount::whereCode($request->discount)->first();//mengecek apakah ada discount yang sedang digunakan
            $data['discount_id'] = $discount->id;//ambil id discount
            $data['discount_percentage'] = $discount->percentage;//ambil presentase discount
        }

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
        $orderId = $checkout->id.'-'.Str::random(5);/*untuk meregenerate order_id yang unique*/
        $price = $checkout->Camp->price*1000;/* dikali 1000 karena harga di database masih dalam ratusan*/

        $checkout->midtrans_booking_code = $orderId;

        
        $item_details[] = [
            'id' => $orderId,
            'price' => $price,
            'quantity' => 1,
            'name' => "Payment for {$checkout->Camp->title} Camp"
        ];
        
        $discountPrice = 0;
        if($checkout->Discount)
        {
            $discountPrice = $price * $checkout->discount_percentage/100;
            $item_details[] = [
                'id' => $checkout->Discount->code,
                'price' => -$discountPrice,
                'quantity' => 1,
                'name' => "Discount {$checkout->Discount->name} {{$checkout->discount_percentage}}%"
            ];
        }

        $total = $price - $discountPrice;

        $transaction_details = [
            'order_id' => $orderId,
            'gross_amount' => $total
        ];

        $userData = [
            "first_name" => $checkout->User->name,
            "last_name" => "",
            "address" => $checkout->User->address,
            "city" => "",
            "postal_code" => "",
            "phone" => $checkout->User->phone,
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
            $paymentUrl = \Midtrans\Snap::createTransaction($midtrans_params)->redirect_url;
            //kalau dapat update tabel checkout yang midtrans URLnya
            $checkout->midtrans_url = $paymentUrl;
            //kalau dapat update tabel checkout yang total
            $checkout->total = $total;
            $checkout->save();

            return $paymentUrl;
        } catch (Exception $e) {
            return false;
        }

    }

    public function midtransCallback(Request $request)
    {
        //notification untuk route get dan post
        $notif = $request->method() == 'POST' ? new Midtrans\Notification() : Midtrans\Transaction::status($request->order_id);

        $transaction_status = $notif->transaction_status;
        $fraud = $notif->fraud_status;

        $checkout_id = explode('-', $notif->order_id)[0];/*ini untuk mengambil checkout_id*/
        $checkout = Checkout::find($checkout_id);/*Mengambil datanya dari database*/

        if ($transaction_status == 'capture') {
            if ($fraud == 'challenge') {
                // TODO Set payment status in merchant's database to 'challenge'
                $checkout->payment_status = 'pending';
            } else if ($fraud == 'accept') {
                // TODO Set payment status in merchant's database to 'success'
                $checkout->payment_status = 'paid';
            }
        } else if ($transaction_status == 'cancel') {
            if ($fraud == 'challenge') {
                // TODO Set payment status in merchant's database to 'failure'
                $checkout->payment_status = 'failed';
            } else if ($fraud == 'accept') {
                // TODO Set payment status in merchant's database to 'failure'
                $checkout->payment_status = 'failed';
            }
        } else if ($transaction_status == 'deny') {
            // TODO Set payment status in merchant's database to 'failure'
            $checkout->payment_status = 'failed';
        } else if ($transaction_status == 'settlement') {
            // TODO set payment status in merchant's database to 'Settlement'
            $checkout->payment_status = 'paid';
        } else if ($transaction_status == 'pending') {
            // TODO set payment status in merchant's database to 'Pending'
            $checkout->payment_status = 'pending';
        } else if ($transaction_status == 'expire') {
            // TODO set payment status in merchant's database to 'expire'
            $checkout->payment_status = 'failed';
        }

        $checkout->save();
        return view('checkout/success');
    }

    public function invoice(Checkout $checkout)
    {
        return $checkout;
    }//untuk return data checkout mengenai program apa yang dibeli
}

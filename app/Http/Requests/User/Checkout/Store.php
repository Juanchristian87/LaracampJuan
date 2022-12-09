<?php

namespace App\Http\Requests\User\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class Store extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check(); //untuk mengecek apakah sudah login atau belum, apabila belum maka tidak dapat masuk ke laman ini 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // $expiredValidation = date('Y-m', time());
        return [
            'name' => 'required|string', /*kenapa string karena nanti data yang diterima menggunakan string*/
            'email' => 'required|email|unique:users,email,'.Auth::id().',id',/*ini jadi email yang digunakan untuk login tidak dikenal sebagai unique lagi tapi bisa digunakan*/
            'occupation' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'discount' => 'nullable|string|exists:discounts,code,deleted_at,NULL'//jadi disini apabila kode promo sudah dihapus maka sudah tidak dapat diakses
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Checkout;
use Auth;

class Camp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'price'];
    /*jadi disini yang di protected atau bisa diisi merupakan title dan price sedangkan slug disini auto create dari title*/

    public function getIsRegisteredAttribute()
    {
        if (!Auth::check())
        {
            return false;
        }
        return Checkout::whereCampId($this->id)->whereUserId(Auth::id())->exists();
        // jadi disini mengambil id yang dipilih dan ambil id yang digunakan pada saat login
    }
}


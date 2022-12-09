<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Checkout;

class PatchCheckoutTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    //ini untuk update price yang masih 0
    public function run()
    {
        $checkouts = Checkout::whereTotal(0)->get();//mengambil price yang 0
        foreach ($checkouts as $key => $checkout)
        {
            $checkout->update([
                'total'=>$checkout->Camp->price
            ]);
        }
    }
}

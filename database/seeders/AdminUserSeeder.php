<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;/*ini untuk import model kita*/

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create(
            [
                'name' => 'admin',
                'email' => 'admin@juan.com',
                'email_verified_at' => date ('Y-m-d H:i:s', time()),
                /*Y = year, m = month, d = tanggal,H = hour, i= minutes, s = second*/
                'password' => \bcrypt('password'), /*ini untuk enkripsi*/
                'is_admin' => true,
            ]
        );
    }
}

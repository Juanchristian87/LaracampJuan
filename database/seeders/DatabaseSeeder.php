<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call
        ([
            CampTableSeeder::class,/*ini untuk memanggil data di camp table seeder dan memasukkannya ke tabel*/
            CampBenefitTableSeeder::class,/*ini untuk memanggil data di camp benefit table seeder dan memasukkannya ke tabel*/
            AdminUserSeeder::class,/*ini untuk memanggil data di admin user seeder dan memasukkannya ke tabel*/
        ]);
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}

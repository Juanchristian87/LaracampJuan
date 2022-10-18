<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) 
        /*data di table ini disesuaikan dengan yang sudah dibuat di whimscal*/
        {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password') -> nullable();/*di set nullable karena tidak butuh password*/
            $table->string('avatar')->nullable();
            $table->string('occupation')->nullable();/*di set null karena admin tidak ada occupation*/
            $table->boolean('is_admin')->default(false);/*ini untuk user biasa jadi defaultnya false*/
            $table->rememberToken();
            $table->timestamps();/*timestamp akan terkonvert secara real time menjadi created_at, updated_at*/
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};

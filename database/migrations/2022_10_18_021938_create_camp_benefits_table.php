<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('camp_benefits', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('camp_id')->unsigned();/*Ini jadi positif*/
            /*atau*/
            /*$table->unsignedbigInteger('camp_id');*/
            $table->string('name');
            $table->timestamps();
            /*foreign key*/
            $table->foreign('camp_id')->references('id')->on('camps')->onDelete('cascade');
            /*berikut jadi kita set camp_id sebagai foreign key yang diambil referensi id dari tabel camps dan menggunakan on delete cascade*/
            /*atau*/
            /*$table->foreignId('camp_id')->constrained();*/
            /*jadi disini dia cara bacanya "camp_id" maka dia langsung referensi ke tabel camp dan langusng ke id */
            /*PERHATIAN DISINI KARENA REFERENCESNYA SAMA KE TABLE CAMP MAKA BARUS BISA DIGUNAKAN APABILA TIDAK MAKA TIDAK BISA*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('camp_benefits');
    }
};

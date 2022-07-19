<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAntrianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('antrian', function (Blueprint $table) {
            $table->id("id_antrian");
            $table->string('nomor_pendaftaran');
            $table->foreignId('id_pasien')->references('id_pasien')->on('pasien')->onDelete('cascade');
            $table->foreignId('id_teknisi')->nullable();
            $table->foreignId('id_admin');
            $table->foreignId('id_pelayanan')->nullable();
            $table->integer('jumlah_gigi')->nullable();
            $table->foreignId('id_status')->nullable();
            $table->foreignId('id_tarif')->nullable();
            $table->integer('total_biaya')->nullable();
            $table->date('tanggal_pelaksanaan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('antrian');
    }
}

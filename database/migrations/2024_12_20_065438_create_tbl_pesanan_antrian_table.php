<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_pesanan_antrian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pesanan');
            $table->integer('nomor_antrian');
            $table->enum('status_antrian', ['menunggu', 'diproses', 'selesai']);
            $table->datetime('waktu_masuk_antrian');
            $table->datetime('waktu_keluar_antrian')->nullable();
            $table->timestamps();
            
            $table->foreign('id_pesanan')->references('id')->on('tbl_pesanan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pesanan_antrian');
    }
};

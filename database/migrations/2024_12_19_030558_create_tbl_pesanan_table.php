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
        Schema::create('tbl_pesanan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_meja');
            $table->unsignedBigInteger('id_kasir');
            $table->string('atas_nama');
            $table->dateTime('waktu_pesan');
            $table->enum('status', ['menunggu', 'diproses', 'siap', 'diantar', 'selesai', 'dibatalkan']);
            $table->decimal('total_harga', 10, 2);
            $table->decimal('pajak', 10, 2);
            $table->text('catatan')->nullable();
            $table->enum('metode_pembayaran', ['tunai', 'kartu_kredit', 'kartu_debit', 'e-wallet']);
            $table->timestamps();
        
            $table->foreign('id_meja')->references('id')->on('tbl_meja');
            $table->foreign('id_kasir')->references('id')->on('users')
                  ->where('role', '=', 'kasir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pesanan');
    }
};

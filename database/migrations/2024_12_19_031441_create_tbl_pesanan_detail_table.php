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
        Schema::create('tbl_pesanan_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pesanan');
            $table->unsignedBigInteger('id_menu');
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 10, 2);
            $table->decimal('diskon', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->text('catatan_khusus')->nullable();
            $table->timestamps();
        
            $table->foreign('id_pesanan')->references('id')->on('tbl_pesanan');
            $table->foreign('id_menu')->references('id')->on('tbl_menu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pesanan_detail');
    }
};

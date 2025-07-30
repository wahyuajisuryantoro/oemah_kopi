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
        Schema::create('tbl_menu', function (Blueprint $table) {
            $table->id();
            $table->string('nama_menu');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_jual', 10, 2);
            $table->decimal('diskon', 5, 2)->default(0);
            $table->integer('waktu_masak'); // dalam menit
            $table->boolean('tersedia')->default(true);
            $table->unsignedBigInteger('id_kategori');
            $table->string('gambar_url')->nullable();
            $table->integer('stok')->default(0);
            $table->decimal('harga_modal', 10, 2);
            $table->timestamps();
            $table->foreign('id_kategori')->references('id')->on('tbl_menu_kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_menu');
    }
};

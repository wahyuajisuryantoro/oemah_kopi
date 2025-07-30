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
        Schema::create('tbl_meja', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_meja');
            $table->integer('kapasitas');
            $table->boolean('tersedia')->default(true);
            $table->string('lokasi')->nullable();
            $table->string('qr_code_token')->unique();
            $table->string('qr_code_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_meja');
    }
};

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
        Schema::table('tbl_pesanan', function (Blueprint $table) {
            $table->enum('status_pembayaran', ['menunggu_pembayaran', 'lunas'])
                  ->default('menunggu_pembayaran')
                  ->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_pesanan', function (Blueprint $table) {
             $table->dropColumn('status_pembayaran');
        });
    }
};

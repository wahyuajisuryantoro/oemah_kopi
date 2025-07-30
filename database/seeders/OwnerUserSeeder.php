<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OwnerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Insert user data
        $userId = DB::table('users')->insertGetId([
            'name' => 'Owner Name',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert user detail data
        DB::table('tbl_users_detail')->insert([
            'user_id' => $userId,
            'nama' => 'Bachtiar',
            'foto' => '',
            'nomor_telepon' => '081234567890',
            'alamat' => 'Jl. Owner No. 123, Kota, Negara',
            'tanggal_bergabung' => Carbon::now()->subYears(1),
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

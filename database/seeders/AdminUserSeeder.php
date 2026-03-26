<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('usuarios')->insert([
            'id_rol'          => 2, // 2 = ADMINISTRADOR
            'nombre_completo' => 'JUNIOR EDIMER ARIAS CASTELLANOS',
            'email'           => 'junior.arias02yt@gmail.com',
            'password_hash'   => Hash::make('qweasdzxc'),
            'estado'          => 1, // 1 = Activo
            'created_at'      => Carbon::now(),
            'updated_at'      => Carbon::now(),
        ]);
    }
}

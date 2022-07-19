<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        $faker = Faker::create();


        DB::table('admin')->insert([
            'username' => 'fathin210',
            'nama' => 'fathin',
            'password' => bcrypt('fathin210')
        ]);
        DB::table('admin')->insert([
            'username' => 'admin',
            'nama' => 'admin',
            'password' => bcrypt('admin')
        ]);

        DB::table('status')->insert([
            'id_status' => '1',
            'jenis_status' => 'Menunggu',
        ]);

        DB::table('status')->insert([
            'id_status' => '2',
            'jenis_status' => 'Sedang Ditangani',
        ]);

        
        DB::table('status')->insert([
            'id_status' => '3',
            'jenis_status' => 'Selesai',
        ]);

        DB::table('status')->insert([
            'id_status' => '4',
            'jenis_status' => 'Batal',
        ]);

        DB::table('pelayanan')->insert([
            "id_pelayanan" => '1',
            "jenis_pelayanan" => 'Pemasangan'
        ]);
        DB::table('pelayanan')->insert([
            "id_pelayanan" => '2',
            "jenis_pelayanan" => 'Perbaikan'
        ]);

        // foreach (range(1,500) as $index){
        //     DB::table('pasien')->insert([
        //         'nomor_pasien' => rand(0,10000),
        //         'nama' => $faker->name(),
        //         'jenis_kelamin' => $faker->randomElement(["L", "P"]),
        //         'alamat' => $faker->address(),
        //         // "no_telepon" => $faker->phoneNumber(),
        //         // 'isDeleted' => false
        //     ]);
        // }

        foreach (range(1, 10) as $index){
            DB::table('teknisi')->insert([
                'nama' => $faker->name(),
                // 'isDeleted' => false
            ]);
        }
        
    }
}

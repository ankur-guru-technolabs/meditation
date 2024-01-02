<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $admin = User::create([
            'name' => 'Admin user',
            'email' => 'james@meetnow.com',
            'phone_no' => '+911234567890',
            'birth_date' => '1999-05-15',
            'gender' => "male",
            'password' => bcrypt('James1234!@#'),
            'user_type' =>'admin',
            'status' => 1
        ]);
    }
}

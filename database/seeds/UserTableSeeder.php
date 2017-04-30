<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Christian Roggia',
            'email' => 'christian.roggia@gmail.com',
            'password' => Hash::make('test132456798'),
            'wallpaper_viewed' => 0,
            'remember_token' => null,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);

        DB::table('users')->insert([
            'id' => 2,
            'name' => 'Matteo Roggia',
            'email' => 'matteo.roggia@gmail.com',
            'password' => Hash::make('qawsed123'),
            'wallpaper_viewed' => 0,
            'remember_token' => null,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);
    }
}

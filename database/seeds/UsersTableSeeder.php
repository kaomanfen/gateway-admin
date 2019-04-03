<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'safe',
                'email' => 'safe@100tal.com',
                'password' => bcrypt('kmfsafe#!'),
                'open_id' => 'test',
            ],

        ]);
    }
}

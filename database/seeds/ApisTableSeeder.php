<?php

use Illuminate\Database\Seeder;

class ApisTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $user = factory(App\Models\Api::class)->make();
        $posts = factory(App\Models\Api::class)->times(22)->make();
        App\Models\Api::insert($posts->toArray());
    }
}

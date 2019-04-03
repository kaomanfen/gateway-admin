<?php

use Illuminate\Database\Seeder;

class AlarmsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $posts = factory(App\Models\Alarms::class)->times(55)->make();
        App\Models\Alarms::insert($posts->toArray());
    }
}

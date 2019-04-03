<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Alarms::class, function (Faker $faker) {
    return [
        'project'=>'a',
        'method'=> $faker->randomElement(['GET','POST']),
        'path'=>$faker->city,
        'request_ids'=>json_encode([$faker->uuid,$faker->uuid,$faker->uuid],true),
        'type'=>$faker->randomElement([1,2]),
        'value'=>$faker->randomElement([1,2]),
        'status'=>$faker->randomElement([0,1]),
        'alram_at'=>$faker->date('Y-m-d H:i:s'),
        'cancle_alarm_at'=>$faker->date('Y-m-d H:i:s'),
    ];
});

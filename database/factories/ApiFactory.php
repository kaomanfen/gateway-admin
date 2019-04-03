<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\Api::class, function (Faker $faker) {
    return [
        'project_id' => $faker->randomElement([1,2]),
        'category_id' => $faker->randomElement([1,2]),
        'name' => $faker->name,
        'client_type' => $faker->randomElement(['app', 'web']),
        'method' => $faker->randomElement(['GET','POST']),
        'version' => 1,
        'creator' => 1,
        'status' => 1,
    ];
});

<?php

$factory->define(App\Models\UserRole::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->name,
        'acl' => ''
    ];
});
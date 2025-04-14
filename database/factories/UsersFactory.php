<?php


$factory->define(App\Models\User::class, function (Faker\Generator $faker) {

    return [
        'firstName' => $faker->firstName,
        'lastName' => $faker->lastName,
        'email' => $faker->email,
        'role_id' => $faker->randomDigit(1,3),
        'password' => \Illuminate\Support\Facades\Hash::make('test-password'),
    ];
});

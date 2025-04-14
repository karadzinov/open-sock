<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
          //  ThermostatSeeder::class,
          //  ThermostatLogSeeder::class,
            UserRolesTableSeeder::class,
          //  UsersTableSeeder::class,
         //   ConfigSeeder::class,
         //   ServerSeeder::class,
            ThermostatTypeSeeder::class,
            CountriesTableSeeder::class
         //   FirstSetUpSeeder::class,
         //   PropertiesSeeder::class
        ]);
    }
}

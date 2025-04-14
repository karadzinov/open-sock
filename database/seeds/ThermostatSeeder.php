<?php

use Illuminate\Database\Seeder;
use App\Models\Thermostat;
use Carbon\Carbon;

class ThermostatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $db_conn = env('DB_CONNECTION');
        if ($db_conn == 'mysql'){

            DB::statement("SET foreign_key_checks=0");
            Thermostat::truncate();
            DB::statement("SET foreign_key_checks=1");
            DB::statement("ALTER TABLE thermostat AUTO_INCREMENT = 1;");
        }else{
            DB::statement("DELETE from thermostat;");
            DB::statement("DELETE FROM SQLITE_SEQUENCE WHERE name='thermostat';");
        }

        $faker = Faker\Factory::create();

        $roomNames = ['Work Room', 'Kitchen', 'Living Room', 'Game Room', 'Boiler', 'Bathroom', 'BedRoom', 'Other'];
        for ($i=0;$i<count($roomNames); $i++) {
            $thermostat = new Thermostat();
            $thermostat->room_name = $roomNames[$i];
            $thermostat->target_temp = rand(18, 31);
            $thermostat->room_temp = rand(18, 31);
            $thermostat->therm_mac_address =  $faker->macAddress();

            $thermostat->save();
        }
    }
}

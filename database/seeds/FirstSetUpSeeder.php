<?php

use Illuminate\Database\Seeder;
use App\Models\FirstSetUpThermostat;

class FirstSetUpSeeder extends Seeder
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
            FirstSetUpThermostat::truncate();
            DB::statement("SET foreign_key_checks=1");
            DB::statement("ALTER TABLE first_set_up_thermostat AUTO_INCREMENT = 1;");
        }else{
            DB::statement("DELETE from first_set_up_thermostat;");
            DB::statement("DELETE FROM SQLITE_SEQUENCE WHERE name='first_set_up_thermostat';");
        }

        $roomNames  = ['Work Room', 'Kitchen', 'Living Room', 'Game Room', 'Boiler', 'Bathroom', 'BedRoom', 'Other'];
        $randIndex  = array_rand($roomNames);
        //First set up
        $set_up = new FirstSetUpThermostat();
        $set_up->user_id = 1;
        $set_up->property_id = 1;
        $set_up->mac_address = "a0:20:a6:13:4b:95";
        $set_up->room_name = $randIndex;
        $set_up->room_area = rand(1.2, 33.5);
        $set_up->mat_power = rand(2.2, 8.5);
        $set_up->thermostat_type_id = rand(1, 6);
        $set_up->temp_limitation = rand(1, 45);

        $set_up->save();
    }
}

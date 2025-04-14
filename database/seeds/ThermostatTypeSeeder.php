<?php

use Illuminate\Database\Seeder;
use App\Models\ThermostatType;

class ThermostatTypeSeeder extends Seeder
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
            ThermostatType::truncate();
            DB::statement("SET foreign_key_checks=1");
            DB::statement("ALTER TABLE thermostat_type AUTO_INCREMENT = 1;");
        }else{
            DB::statement("DELETE from thermostat_type;");
            DB::statement("DELETE FROM SQLITE_SEQUENCE WHERE name='thermostat_type';");
        }

        $ther_type_names = ['floor_sensor', 'room_sensor', 'room_sensor_floor_limitation', 'bathroom_mode', 'water_pipe_heating_pump', 'boiler'];

        for($i=0; $i<count($ther_type_names); $i++){

            $ther_type = new ThermostatType();
            $ther_type->name = $ther_type_names[$i];

            $ther_type->save();
        }
    }
}

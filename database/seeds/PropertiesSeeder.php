<?php

use Illuminate\Database\Seeder;
use App\Models\Properties;

class PropertiesSeeder extends Seeder
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
            Properties::truncate();
            DB::statement("SET foreign_key_checks=1");
            DB::statement("ALTER TABLE first_set_up_thermostat AUTO_INCREMENT = 1;");
        }else{
            DB::statement("DELETE from properties;");
            DB::statement("DELETE FROM SQLITE_SEQUENCE WHERE name='properties';");
        }
        
        //Create fake properties
        $property = new Properties();
        $property->user_id = 1;
        $property->number_of_therm = rand(1,5);
        $property->square_measure = 'm';
        $property->square_meters = rand(00.00, 55.00);
        $property->temp_unit = 'C';
        
        $property->save();
    }
}

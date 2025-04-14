<?php

use Illuminate\Database\Seeder;
use App\Models\ThermostatLog;
use Carbon\Carbon;

class ThermostatLogSeeder extends Seeder
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
            ThermostatLog::truncate();
            DB::statement("SET foreign_key_checks=1");
            DB::statement("ALTER TABLE thermostatLog AUTO_INCREMENT = 1;");
        }else{
            DB::statement("DELETE from thermostatLog;");
            DB::statement("DELETE FROM SQLITE_SEQUENCE WHERE name='thermostatLog';");
        }
       
        for ($i=0;$i<15; $i++) {
            $thermostat = new ThermostatLog();
            $thermostat->set = rand(18, 31);    
            $thermostat->ambient = rand(18, 31);  
            $thermostat->date =  Carbon::now()->format('Y-m-d'); 
            $thermostat->thermostatId = rand(1, 6);
            
            $thermostat->save();
        }
    }
}

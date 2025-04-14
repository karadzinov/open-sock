<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('config')->insert([
            'key' => "num_of_connection",
            'value' =>  0,
            'created_at' => Carbon::now(),
        ]);
    }
}

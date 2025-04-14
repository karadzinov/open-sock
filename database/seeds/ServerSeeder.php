<?php

use Illuminate\Database\Seeder;
use App\Models\Server;

class ServerSeeder extends Seeder
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
            Server::truncate();
            DB::statement("SET foreign_key_checks=1");
            DB::statement("ALTER TABLE servers AUTO_INCREMENT = 1;");
        }else{
            DB::statement("DELETE from servers;");
            DB::statement("DELETE FROM SQLITE_SEQUENCE WHERE name='servers';");
        }

        $faker = Faker\Factory::create();

        for ($i=0;$i<4; $i++) {
            $server = new Server();
            $server->ip_address = $faker->ipv4();
            $server->port = $faker->numberBetween(1000, 90000);    
            $server->host = $faker->domainName();  
            $server->description =  $faker->text(50); 
            
            $server->save();
        }
    }
}

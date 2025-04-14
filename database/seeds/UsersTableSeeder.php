<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
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
            User::truncate();
            DB::statement("DELETE from thermostat_user;");
            DB::statement("SET foreign_key_checks=1");
            DB::statement("ALTER TABLE users AUTO_INCREMENT = 1;");
            DB::statement("ALTER TABLE thermostat_user AUTO_INCREMENT = 1;");
            
        }else{
            DB::statement("DELETE from users;");
            DB::statement("DELETE FROM SQLITE_SEQUENCE WHERE name='users';");
        }
        // Seed test user
        //Admin  User
        $user = new User();
        $user->firstName = 'martin';
        $user->lastName = 'karadzinov';
        $user->email = 'martin@karadzinov.com';
        $user->role_id = 1;
        $user->password = Hash::make('owe04gus');

        $user->save();
       
        //Users
        $users = ['nikola', 'igor', 'goce'];
        $lastName = ['notevski', 'varsamovski', 'jovancevski'];
        for ($i=0; $i < count($users); $i++) {
            $user = new User();
            $user->firstName = $users[$i];
            $user->lastName  = $lastName[$i];
            $user->email     = $users[$i].'@'.$users[$i].'.com';
            $user->role_id   = 2;
            $user->password  = Hash::make('owe04gus');
            
            $user->save();
        }

        //Guest User
        $user = new User();
        $user->firstName = 'stefan';
        $user->lastName = 'perovski';
        $user->email = 'stefan@perovski.com';
        $user->role_id = 3;
        $user->password = Hash::make('owe04gus');

        $user->save();

        $all_users = User::all();
        foreach($all_users as $user)
        {
            $user->thermostats()->attach(rand(1,6));
        }
    }
}

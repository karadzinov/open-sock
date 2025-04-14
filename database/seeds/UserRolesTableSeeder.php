<?php

use Illuminate\Database\Seeder;
use App\Models\UserRole;
class UserRolesTableSeeder extends Seeder
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
            UserRole::truncate();
            DB::statement("SET foreign_key_checks=1");
            DB::statement("ALTER TABLE user_roles AUTO_INCREMENT = 1;");
        }else{
            DB::statement("DELETE from user_roles;");
            DB::statement("DELETE FROM SQLITE_SEQUENCE WHERE name='user_roles';");
        }

        factory(\App\Models\UserRole::class)->create([
            'name' => 'Technician',
            'acl' => ''
        ]);

        factory(\App\Models\UserRole::class)->create([
            'name' => 'Admin',
            'acl' => 'thermostats:index,view|users:index,view'
        ]);

        factory(\App\Models\UserRole::class)->create([
            'name' => 'Guest',
            'acl' => 'thermostats:index,view'
        ]);

        factory(\App\Models\UserRole::class)->create([
            'name' => 'Unverified',
            'acl' => 'thermostats:view'
        ]);
        factory(\App\Models\UserRole::class)->create([
            'name' => 'Unactivated',
            'acl' => 'thermostats:view'
        ]);
    }
}

<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


/*** Api Routes ***/


/* Register Route */
$router->post('/register', [
    'as'   => 'register',
    'uses' => 'Api\RegisterController@store',
]);

/* Return New Token */
$router->post('refresh-token', [
    'as'   => 'refresh.token',
    'uses' => 'Api\AuthController@refreshToken',
]);

$router->post('login', [
    'as'   => 'login',
    'uses' => 'Api\LoginController@authenticate',
]);

$router->get('countries', [
    'as'   => 'login',
    'uses' => 'Api\LoginController@countries',
]);


$router->group(
    ['prefix' => 'api', 'middleware' => ['auth:api']],
    function () use ($router) {

        /* Return Auth User */
        $router->get('/user', [
            'as'         => 'me',
            'uses'       => 'Api\UserController@index',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Users List */
        $router->get('/users', [
            'as'         => 'users',
            'uses'       => 'Api\UserController@users',
            'middleware' => 'scope:sapo',
        ]);

        /* Return User by Id */
        $router->get('users/{id}', [
            'as'         => 'user.get',
            'uses'       => 'Api\UserController@view',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Stored User */
        $router->post('users', [
            'as'         => 'user.store',
            'uses'       => 'Api\UserController@store',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Updated User */
        $router->put('users/{id}', [
            'as'         => 'user.update',
            'uses'       => 'Api\UserController@update',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Deleted User */
        $router->delete('users/{id}', [
            'as'         => 'user.delete',
            'uses'       => 'Api\UserController@delete',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Deleted User */
        $router->post('users/detach/{property_id}/{id}', [
            'as'         => 'user.delete',
            'uses'       => 'Api\UserController@detachUser',
            'middleware' => 'scope:sapo',
        ]);


        /* Return User Language*/
        $router->post('/language', [
            'as'         => 'user.lang',
            'uses'       => 'Api\UserController@setLang',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Deleted User */
        $router->post('users/search', [
            'as'         => 'user.search',
            'uses'       => 'Api\UserController@search',
            'middleware' => 'scope:sapo',
        ]);

        /* Logs */
        $router->post('/logs', [
            'as'         => 'logs',
            'uses'       => 'Api\UserController@logs',
            'middleware' => 'scope:sapo,admin',
        ]);

        /* Invite user */
        $router->post('invite', [
            'as'         => 'user.store',
            'uses'       => 'Api\UserController@invite',
            'middleware' => 'scope:sapo,admin',
        ]);

        /* Return Thermostat List */
        $router->get('/thermostats', [
            'as'         => 'thermostats',
            'uses'       => 'Api\ThermostatController@index',
            'middleware' => 'scope:sapo',
        ]);

        /* Return All Thermostat List */
        $router->get('/all-thermostats', [
            'as'         => 'all.thermostats',
            'uses'       => 'Api\ThermostatController@thermostats',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Thermostat by Id */
        $router->get('thermostats/{id}', [
            'as'         => 'thermostats.get',
            'uses'       => 'Api\ThermostatController@view',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Stored Thermostat */
        $router->post('thermostats', [
            'as'         => 'thermostats.store',
            'uses'       => 'Api\ThermostatController@store',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Updated Thermostat */
        $router->put('thermostats/{id}', [
            'as'         => 'thermostats.update',
            'uses'       => 'Api\ThermostatController@update',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Deleted Thermostat */
        $router->delete('thermostats/{id}', [
            'as'         => 'thermostats.delete',
            'uses'       => 'Api\ThermostatController@delete',
            'middleware' => 'scope:sapo',
        ]);


        /* Return Deleted Thermostat */
        $router->post('thermostats/{id}/refresh', [
            'as'         => 'thermostats.refresh.all',
            'uses'       => 'Api\ThermostatController@sentAllCommands',
            'middleware' => 'scope:sapo',
        ]);


        /* Return Deleted User */
        $router->delete('first-setup/delete/{mac_address}', [
            'as'         => 'thermostat.delete.first.setup',
            'uses'       => 'Api\ThermostatController@deleteFirstSetup',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Auth User Role */
        $router->get('roles', [
            'as'         => 'role',
            'uses'       => 'Api\UserRoleController@index',
            'middleware' => 'scope:sapo',
        ]);

        /* Return All Roles */
        $router->get('all-roles', [
            'as'         => 'roles.all',
            'uses'       => 'Api\UserRoleController@roles',
            'middleware' => 'scope:sapo',
        ]);

        /* Return New Role */
        $router->post('roles', [
            'as'         => 'roles.store',
            'uses'       => 'Api\UserRoleController@store',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Updated Role */
        $router->put('roles/{id}', [
            'as'         => 'roles.update',
            'uses'       => 'Api\UserRoleController@update',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Role Id */
        $router->get('roles/{id}', [
            'as'         => 'roles.view',
            'uses'       => 'Api\UserRoleController@view',
            'middleware' => 'scope:sapo',
        ]);

        /* Return [] For Deleted Role */
        $router->delete('roles/{id}', [
            'as'         => 'roles.delete',
            'uses'       => 'Api\UserRoleController@delete',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Servers List */
        $router->get('/servers', [
            'as'         => 'servers',
            'uses'       => 'Api\ServerController@index',
            'middleware' => 'scope:admin',
        ]);

        /* Return Create Server */
        $router->post('/create-server', [
            'as'         => 'create_server',
            'uses'       => 'Api\ServerController@store',
            'middleware' => 'scope:admin',
        ]);

        /* Return Updated Servers */
        $router->put('update-server/{id}', [
            'as'         => 'update_server',
            'uses'       => 'Api\ServerController@update',
            'middleware' => 'scope:admin',
        ]);

        /* Return Deleted Servers */
        $router->delete('delete-server/{id}', [
            'as'         => 'delete_server',
            'uses'       => 'Api\ServerController@delete',
            'middleware' => 'scope:admin',
        ]);

        /* Return Create Server */
        $router->post('/set-commands', [
            'as'         => 'set_commands',
            'uses'       => 'Api\ThermostatController@sentCommands',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Scheduler */
        $router->post('/set-scheduler/{thermostat_id}', [
            'as'         => 'set_scheduler',
            'uses'       => 'Api\ThermostatController@scheduler',
            'middleware' => 'scope:sapo',
        ]);

        $router->get('/get-scheduler/{id}', [
            'as'         => 'set_scheduler',
            'uses'       => 'Api\ThermostatController@getScheduler',
            'middleware' => 'scope:sapo',
        ]);

        /* Return User Properties */
        $router->get('/properties', [
            'as'         => 'properties',
            'uses'       => 'Api\PropertiesController@index',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Users from selected Property */
        $router->get('/properties/users', [
            'as'         => 'properties.users',
            'uses'       => 'Api\PropertiesController@users',
            'middleware' => 'scope:sapo',
        ]);

        /* Return User Properties with selected Property */
        $router->get('/properties/{id}', [
            'as'         => 'properties.get',
            'uses'       => 'Api\PropertiesController@show',
            'middleware' => 'scope:sapo',
        ]);

        /* Return User Properties with selected Property */
        $router->get('/properties/{id}/users', [
            'as'         => 'properties.user.get',
            'uses'       => 'Api\PropertiesController@getProperties',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Created Properties */
        $router->post('/properties', [
            'as'         => 'properties.create',
            'uses'       => 'Api\PropertiesController@store',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Updated Properties */
        $router->put('/properties/{id}', [
            'as'         => 'properties.update',
            'uses'       => 'Api\PropertiesController@update',
            'middleware' => 'scope:sapo',
        ]);

        /* Delete Properties */
        $router->delete('/properties/{id}', [
            'as'         => 'properties.delete',
            'uses'       => 'Api\PropertiesController@delete',
            'middleware' => 'scope:sapo',
        ]);


        /* Return Created First Set Up */
        $router->post('/first-set-up', [
            'as'         => 'first_set_up',
            'uses'       => 'Api\ThermostatController@firstSetUpThermostat',
            'middleware' => 'scope:sapo',
        ]);

        /* Return All thermostat types */
        $router->get('/get-all-thermostat-types', [
            'as'         => 'get_all_thermostat_types',
            'uses'       => 'Api\ThermostatController@getAllThermostatType',
            'middleware' => 'scope:sapo',
        ]);

        /* Return thermostat by Mac Address */
        $router->get('/thermostat/{mac_address}', [
            'as'         => 'thermostat.mac.address',
            'uses'       => 'Api\ThermostatController@getThermostatByMac',
            'middleware' => 'scope:sapo',
        ]);

        /* Return all thermostats for property */
        $router->post('/set-all-thermostats-status', [
            'as'         => 'set_all_thermostats_status',
            'uses'       => 'Api\ThermostatController@setAllThermostatStatus',
            'middleware' => 'scope:sapo',
        ]);

        /* Return all thermostats for property */
        $router->post('/total-property-consumption', [
            'as'         => 'total_property_consumption',
            'uses'       => 'Api\PropertiesController@getTotalPropertyConsumption',
            'middleware' => 'scope:sapo',
        ]);

        /* Return FAQ */
        $router->get('/faq', [
            'as'         => 'faq',
            'uses'       => 'Api\FaqController@index',
            'middleware' => 'scope:sapo',
        ]);

        /* Return FAQ by Id */
        $router->get('faq/{id}', [
            'as'         => 'faq.get',
            'uses'       => 'Api\FaqController@view',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Stored FAQ */
        $router->post('faq', [
            'as'         => 'faq.store',
            'uses'       => 'Api\FaqController@store',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Updated FAQ */
        $router->put('faq/{id}', [
            'as'         => 'faq.update',
            'uses'       => 'Api\FaqController@update',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Deleted FAQ */
        $router->delete('faq/{id}', [
            'as'         => 'faq.delete',
            'uses'       => 'Api\FaqController@delete',
            'middleware' => 'scope:sapo',
        ]);


        /* Return Main Stats */
        $router->get('dashboard', [
            'as'         => 'dashboard',
            'uses'       => 'Api\DashboardController@index',
            'middleware' => 'scope:sapo',
        ]);

        /* Return Technitians */
        $router->get('dashboard/technicians', [
            'as'         => 'dashboard',
            'uses'       => 'Api\DashboardController@getTechnicians',
            'middleware' => 'scope:sapo',
        ]);

        /* Delete Technitians */
        $router->post('dashboard/technicians/{id}', [
            'as'         => 'dashboard',
            'uses'       => 'Api\DashboardController@deleteTechnicians',
            'middleware' => 'scope:sapo',
        ]);

        /* Return System Stats */
        $router->get('system', [
            'as'         => 'system',
            'uses'       => 'Api\DashboardController@getSysInfo',
            'middleware' => 'scope:sapo',
        ]);


    }
);

//Dashboard view
// $router->get('/', 'DashboardController@index');
$router->get('/weather', 'DashboardController@weather');

$router->get('/test', 'DashboardController@test');
$router->get('/pusher', 'DashboardController@pusher');

$router->get('/test', 'DashboardController@getSetTemp');
$router->get('/set-temp-ajax', 'DashboardController@getSetTempAjax');

$router->get('/sys-info', [
    'as'   => 'sys-info',
    'uses' => 'ApiController@get_sysinfo',
]);

$router->get('/thermostats', 'DashboardController@thermostats');
$router->get('/thermostat/{id}', 'DashboardController@thermostat_id');
$router->get('/thermostat/master/{id}', 'Api\ThermostatController@checkMaster');
$router->get('/log', 'DashboardController@listen');
$router->post('/log', 'DashboardController@receive');
$router->get('/scheduler_test', 'DashboardController@scheduler_test');
$router->get('/',
    function () {
        return view('login');
    });

$router->get('/button',
    function () {
        return view('testbutton');
    });
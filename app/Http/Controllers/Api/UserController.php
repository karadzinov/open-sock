<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Properties;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource as UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Logs;
use Illuminate\Http\File;
use Nexmo\User\Collection;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Models\Countries;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public $error;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->error = [
            'status' => 'ERROR',
            'error'  => '404 not found',
        ];
    }


    /**
     * @SWG\Get(
     *   path="/api/user",
     *   summary="Get Auth User",
     *   tags={"Users"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Response(
     *     response=200,
     *     description="Success"
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Validation Errors"
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error"
     *   )
     * )
     */

    public function index(Request $request)
    {
        $user = $request->user();
        $user = new UserResource($user);

        return response()->json($user, 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/users",
     *   summary="Get All Users",
     *   tags={"Users"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Response(
     *     response=200,
     *     description="Success"
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Validation Errors"
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error"
     *   )
     * )
     */

    public function users()
    {
        $users = UserResource::collection(User::all());

        return response()->json($users, 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/users/{id}",
     *   summary="Get Specific User",
     *   tags={"Users"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success"
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Validation Errors"
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error"
     *   )
     * )
     */

    public function view($id)
    {
        $user = User::where('id', $id)->with('role')->first();

        if (!$user) {
            return response()->json($this->error, 404);
        }

        $user = new UserResource($user);

        return response()->json($user, 200);

    }

    /**
     * @SWG\Post(
     *   path="/api/users",
     *   summary="Store user",
     *   tags={"Users"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="phone",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="country",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="role_id",
     *     in="formData",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success"
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Validation Errors"
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error"
     *   )
     * )
     */

    public function store(Request $request)
    {
        $v = validator($request->only(
            'email',
            'name',
            'password',
            'country',
            'phone'
        ), [
            'name'  => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:6',
            'country' => 'required_with:phone',
            'phone'   => 'required|phone:AUTO,US,country',
        ],
            [
                'phone' => '$e->getMessage()',
            ]
        );
        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $country = Countries::where('iso_3166_2', '=', $request['country'])->first();
        $phone = PhoneNumber::make($request['phone'], $request['country']);

        $data = $request->only(
            'email',
            'name',
            'password',
            'role_id',
            'country',
            'phone');

        $role_id = $request->has('role_id') ? $role_id = $request->get('role_id') : '1';


        $user = User::create([
            'name' => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role_id'   => $role_id,
            'country'   => $country->id,
            'phone'     => $phone
        ]);

        $user = new UserResource($user);

        return response()->json($user, 200);
    }

    /**
     * @SWG\Put(
     *   path="/api/users/{id}",
     *   summary="Update user",
     *   tags={"Users"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="phone",
     *     in="formData",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="country",
     *     in="formData",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="role_id",
     *     in="formData",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success"
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Validation Errors"
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error"
     *   )
     * )
     */

    public function update(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json($this->error, 404);
        }

        $input = $request->all();
        $user->fill($input)->save();

        $user = new UserResource($user);

        return response()->json($user, 200);
    }

    /**
     * @SWG\Delete(
     *   path="/api/users/{id}",
     *   summary="Delete user",
     *   tags={"Users"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success"
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Validation Errors"
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error"
     *   )
     * )
     */

    public function delete($id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json($this->error, 404);
        }

        $user->delete();

        return response()->json([], 200);
    }


    /**
     * @SWG\Post(
     *   path="/language",
     *   summary="Invite user to property",
     *   tags={"Users"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="lang",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success"
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Validation Errors"
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error"
     *   )
     * )
     */

    public function setLang(Request $request)
    {

        $v = validator($request->only(
            'lang'
        ), [
            'lang'  => 'required|string|max:2',
         ]
        );

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $user = User::where('id', '=', $request->user()->id)->first();
        if($user->lang != $request->get('lang'))
        {
            $user->lang = $request->get('lang');
            $user->save();
        }

        $user = new UserResource($user);

        return response()->json($user, 200);
    }

    /**
     * @SWG\Post(
     *   path="/api/invite",
     *   summary="Invite user to property",
     *   tags={"Users"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="phone",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="property_id",
     *     in="formData",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success"
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Validation Errors"
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error"
     *   )
     * )
     */

    public function invite(Request $request)
    {

        $user = $request->user();


        $existingUsers  = [];
        $newUsers       = [];

        $requests = $request->all();




        try {
            $record = app()->geoip->getIp();
            $record = app()->geoip->getLocation($record);

            $cc = json_encode($record->country);
            $cc = json_decode($cc);
            $country_code = $cc->iso_code;

        }
        catch(\Exception $e)
        {
            $country_code = 'IL';
        }



        $country = Countries::select('id', 'iso_3166_2 as country', 'name')->where('iso_3166_2', '=', $country_code)->first();

        $country = isset($country) ? $country : 'IL';


        foreach ($requests as $request)
        {

            $roles = [
                'name'        => 'required|string',
                'property_id' => 'required|integer',
            ];

            $v = validator($request, $roles);
            if ($v->fails()) {
                return response()->json($v->errors()->all(), 400);
            }


            $property = Properties::where('id', '=', $request['property_id'])->first();


            if(!$property)
            {
                return response()->json(['error' => 'There is no such property'], 401);
            }

            if($property->user_id != \Auth::user()->id)
            {
                return response()->json(['error' => 'You are not authorized to invite users to this property'], 400);
            }


            $phone = PhoneNumber::make($request['phone'], $country->country);

            $existingUser = User::where('phone', '=', $phone)->first();

            if ($existingUser) {




                $nexmo = app('Nexmo\Client');


                if($country->country === 'IL' || $country === 'IL')
                {
                    try {
                        header('Content-Type: text/html; charset=utf-8');
                        $nexmo->message()->send([
                            'to' => $phone,
                            'from' => 'TouchApp',
                            'type' => 'unicode',
                            'text' => ' שלום ,' . $existingUser->name . '  נוספת בהצלחה לבית חדש ע"י . ' . $user->name,
                        ]);
                    }
                    catch (\Exception $e)
                    {
                        $error = ['Invalid phone number'];
                        return response()->json($error, 400);
                    }
                }
                else {
                    try {
                        $nexmo->message()->send([
                            'to'   => $phone,
                            'from' => 'TouchApp',
                            'type' => 'unicode',
                            'text' => 'Dear '.$existingUser->name.' You have been added to new property by ' . $user->name,
                        ]);
                    }
                    catch (\Exception $e)
                    {
                        $error = ['Invalid phone number'];
                        return response()->json($error, 400);
                    }

                }

                $existingUser->thermostats()->syncWithoutDetaching($property->thermostats);
                $existingUser->properties()->syncWithoutDetaching($request['property_id']);


                if($user->role_id === 1) {
                    $property->user_id = $existingUser->id;
                    $property->save();
                }

                $existingUser = new UserResource($existingUser);

                $existingUsers[] = $existingUser;

            } else {

                $newUser = new User();
                $newUser->name = $request['name'];
                $newUser->phone = $phone;
                $newUser->email = str_replace('+', '', $phone)."@touchapp.com";
                $newUser->country = $country->id;
                $password = str_random(8);
                $newUser->password = Hash::make($password);
                $newUser->role_id = 3;
                $newUser->save();

                if($user->role_id === 1) {
                    $newUser->role_id = 2;
                    $newUser->save();
                    $property->user_id = $newUser->id;
                    $property->save();
                }


                $to = User::Find($newUser->id);





                $nexmo = app('Nexmo\Client');


                if($country->country === 'IL' || $country === 'IL')
                {
                    try {
                        header('Content-Type: text/html; charset=utf-8');
                        $nexmo->message()->send([
                            'to'   => $phone,
                            'from' => 'TouchApp',
                            'type' => 'unicode',
                            'text' => 'המשתמש '.$to->name
                                . ', ' .$user->name. ' הזמין אותך להוריד את TouchApp',
                        ]);
                    }
                    catch (\Exception $e)
                    {
                        $error = ['Invalid phone number'];
                        return response()->json($error, 400);
                    }

                }
                else {
                    try {
                        $nexmo->message()->send([
                            'to'   => $phone,
                            'from' => 'TouchApp',
                            'type' => 'unicode',
                            'text' => 'Dear '.$to->name
                                . ', ' .$user->name. ' is inviting you to download TouchAPP',
                        ]);
                    }
                    catch (\Exception $e)
                    {
                        $error = ['Invalid phone number'];
                        return response()->json($error, 400);
                    }

                }


                $to->thermostats()->syncWithoutDetaching($property->thermostats);
                $to->properties()->syncWithoutDetaching($request['property_id']);




                $newUser = new UserResource($newUser);

                $newUsers[] = $newUser;

            }

        }


        $data = ["newUsers" => $newUsers, "existingUsers" => $existingUsers];

        return response()->json($data, 200);

    }

    /**
     * @SWG\Post(
     *   path="/api/users/detach/{property_id}/{user_id}",
     *   summary="Detach User from Property",
     *   tags={"Users"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="property_id",
     *     in="path",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="user_id",
     *     in="path",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success"
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Validation Errors"
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error"
     *   )
     * )
     */

    public function detachUser($property_id, $user_id)
    {
        $property = Properties::find($property_id)->first();
        DB::delete('delete from properties_user where properties_id=? and user_id=?', [$property_id, $user_id]);
        $property->users()->detach($user_id);

        $users = UserResource::collection($property->users);
        return response()->json($users, 200);
    }

    /**
     * @SWG\Post(
     *   path="/api/logs",
     *   summary="Android Logs",
     *   tags={"Logs"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="data",
     *     in="path",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success"
     *   ),
     *   @SWG\Response(
     *     response=400,
     *     description="Validation Errors"
     *   ),
     *   @SWG\Response(
     *     response=500,
     *     description="Internal Server Error"
     *   )
     * )
     */

    public function logs(Request $request)
    {


        $all = $request->files;


        foreach ($all as $file) {

            $contents = file_get_contents($file);

            $dateNow = Carbon::now();
            $data = str_replace(' ', '_', $dateNow->toDateTimeString());
            $data = str_replace(':', '_', $data);

            $filename = $data.'_log.txt';
            Storage::disk('local')->put($filename, gzdecode($contents));


            $log = new Logs();
            $log->user_id = \Auth::user()->id;
            $log->log = gzdecode($contents);
            $log->file_name = $filename;
            $log->save();
        }


        return response()->json([], 200);

    }

    public function search(Request $request)
    {
        $keyword = $request->get('keyword');
        $users = User::where('phone', 'LIKE', '%'.$keyword.'%')->orWhere('name', 'LIKE',  '%'.$keyword.'%')->limit(10)->get();
        return response()->json($users, 200);
    }
}

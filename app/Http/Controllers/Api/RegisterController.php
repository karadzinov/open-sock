<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Mail;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Models\Countries;


use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class RegisterController extends Controller
{

    /**
     * @SWG\Post(
     *   path="/register",
     *   summary="Register username to generate Bearer token",
     *   tags={"Auth"},
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
     *     name="country",
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
    public function store(Request $request)
    {


        $country = Countries::where('iso_3166_2', '=', $request['country'])->first();

        if(!$country) {
            return response()->json(["Invalid country"], 401);
        }

        $v = validator($request->only(
            'name',
            'phone',
            'country'
        ), [
            'name'    => 'required|string|max:255|min:3',
            'country' => 'required_with:phone',
            'phone'   => 'required|phone:AUTO,US,country',
        ],
            [
                'phone' => 'Invalid phone number',
            ]
        );

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 401);
        }


        $data = $request->all();


        $code = strtoupper(str_random(5));

        $phone = PhoneNumber::make($request['phone'], $request['country']);





        $user = User::where('phone', $phone)->where('country', $country->id)
            ->with('role')
            ->first();


        if (!$user) {
            $user = User::create([
                'name'     => $data['name'],
                'phone'    => $phone,
                'email'    => str_replace('+', '', $phone)."@touchapp.com",
                'password' => Hash::make($code),
                'code'     => $code,
                'country'  => $country->id,
                'role_id'  => '2',
            ]);
        } else {
            $user->password = Hash::make($code);
            $user->code = $code;
            $user->save();
        }

        try {

                $nexmo = app('Nexmo\Client');

                $nexmo->message()->send([
                    'to'   => $phone,
                    'from' => 'TouchApp',
                    'type' => 'unicode',
                    'text' => 'Dear '.$user->name.' Your verification code is: '
                        .$code,
                ]);

        }
        catch(\Exception $e)
        {

        }

        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('firstSetup.log', Logger::WARNING));

        $log->warning($user);

        $user = UserResource::make($user);

        return response()->json($user, 200);

    }

}
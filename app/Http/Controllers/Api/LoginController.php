<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Models\Countries;


class LoginController extends Controller
{

    /**
     * @SWG\Get(
     *   path="/countries",
     *   summary="Get countries list",
     *   tags={"Auth"},
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

    function cmp($a, $b)
    {
        if ($a == $b) {
            return 0;
        }

        return ($a < $b) ? -1 : 1;
    }

    public function countries()
    {


        try {
            $record = app()->geoip->getIp();
            $record = app()->geoip->getLocation($record);

            $cc = json_encode($record->country);
            $cc = json_decode($cc);
            $country_code = $cc->iso_code;

        }
        catch(\Exception $e)
        {
            $country_code = 'MK';
        }


        $countries = Countries::select('iso_3166_2 as country', 'name', 'calling_code')
            ->orderby('name')->get();

        $r = [];
        $c = [];
        foreach ($countries as $country) {
            if ($country['country'] === $country_code) {
                $c[] = $country;
            } else {
                $r[] = $country;
            }
        }

        $countries = array_merge($c, $r);

        return response()->json($countries, 200);
    }

    /**
     * @SWG\Post(
     *   path="/login",
     *   summary="Login user with phone and code",
     *   tags={"Auth"},
     *   @SWG\Parameter(
     *     name="phone",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="code",
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

    public function authenticate(Request $request)
    {
        $v = validator($request->only(
            'phone',
            'code',
            'country'
        ), [
            'code'    => 'required|string|min:5',
            'phone'   => 'required|phone:AUTO,US,country',
            'country' => 'required_with:phone',
        ],
            [
                'phone' => 'Invalid phone number',
            ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }


        $country = Countries::where('iso_3166_2', '=', $request['country'])
            ->first();

        if(!$country)
        {
            return response()->json('Invalid country', 401);
        }


        $code = $request->input('code');

        $phone = PhoneNumber::make($request['phone'], $request['country']);

        $user = User::where('phone', $phone)->where('code', $code)->with('role')
            ->first();

        if (!$user) {
            return response()->json(["error" => "Invalid code"], 401);
        }

        $client = Client::where('password_client', 1)->first();

        $user->password = Hash::make($code);

        if ($user->role_id === '4') {
            $user->role_id = 2;
        }
        $user->code = null;
        $user->save();

        $proxy = Request::create(
            '/oauth/token',
            'post',
            [
                'grant_type'    => 'password',
                'client_id'     => $client->id,
                'client_secret' => $client->secret,
                'username'      => $user->email,
                'password'      => $code,
                'scope'         => ['sapo'],
            ]
        );

        global $app;

        $tokenInfo = $app->dispatch($proxy)->getContent();

        $user = UserResource::make($user);

        return response()->json([
            "user"  => $user,
            "token" => json_decode($tokenInfo),
        ], 200);

    }
}

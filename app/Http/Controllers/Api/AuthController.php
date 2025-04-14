<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Passport\Client;


class AuthController extends Controller
{

    /**
     * @SWG\Post(
     *   path="/refresh-token",
     *   summary="Get new access token",
     *   tags={"Auth"},
     *   @SWG\Response(
     *     response=200,
     *     description="Working"
     *   ),
     *   @SWG\Parameter(
     *     name="refresh_token",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   )
     * )
     */

    public function refreshToken(Request $request)
    {

        $v = validator($request->only(
            'refresh_token'
        ), [
                'refresh_token' => 'required|string',
            ]
        );

        $data = $request->only('refresh_token');

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $client = Client::where('password_client', 1)->first();

        $proxy = Request::create(
            '/oauth/token',
            'post',
            [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $data['refresh_token'],
                'client_id'     => $client->id,
                'client_secret' => $client->secret,
                'scope'         => null,
            ]
        );

        global $app;

        $info = $app->dispatch($proxy);

        $checkError = json_decode($info->getContent());
        if (isset($checkError->error)) {
            return response()->json($checkError, 419);
        } else {
            return $info;
        }


    }
}
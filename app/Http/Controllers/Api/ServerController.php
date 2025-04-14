<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Server;


class ServerController extends Controller
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
     *   path="/api/servers",
     *   summary="Get Servers",
     *   tags={"Servers"},
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

    public function index()
    {
        $servers = Server::all();
        return response()->json($servers, 200);
    }

    /**
     * @SWG\Post(
     *   path="/api/servers",
     *   summary="Store Server",
     *   tags={"Servers"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="ip_address",
     *     in="formData",
     *     required=true,
     *     format="ipv4",
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="port",
     *     in="formData",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="host",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="description",
     *     in="formData",
     *     required=false,
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
        $v = validator($request->only(
            'ip_address',
            'port',
            'host'
        ), [
            'ip_address' => 'required|ipv4',
            'port'  => 'required|integer',
            'host'     => 'required|string|max:255'
        ],[
            'ipv4' => 'The Ip Address is not valid.'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $server = Server::create([
            'ip_address' => $request->request->get('ip_address'),
            'port'  => $request->request->get('port'),
            'host'     => $request->request->get('host'),
            'description'  => $request->request->get('description'),
        ]);


        return response()->json($server, 200);
    }

    /**
     * @SWG\Put(
     *   path="/api/update-server/{id}",
     *   summary="Update server",
     *   tags={"Servers"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="ip_address",
     *     in="formData",
     *     required=false,
     *     format="ipv4",
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="port",
     *     in="formData",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="host",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="description",
     *     in="formData",
     *     required=false,
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

    public function update(Request $request, $id)
    {
        $server = Server::find($id);

        if (!$server) {
            return response()->json($this->error, 404);
        }

        $v = validator($request->only(
            'ip_address',
            'port',
            'host'
        ), [
            'ip_address' => 'ipv4',
            'port'  => 'integer',
            'host'     => 'string|max:255'
        ],[
            'ipv4' => 'The Ip Address is not valid.'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $input = $request->all();
        $server->fill($input)->save();

        return response()->json($server, 200);
    }

    /**
     * @SWG\Delete(
     *   path="/api/delete-server/{id}",
     *   summary="Delete server",
     *   tags={"Servers"},
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
        $server = Server::find($id);

        if (!$server) {
            return response()->json($this->error, 404);
        }

        $server->delete();
        return response()->json(['Successfully Deleted'], 200);
    }
}

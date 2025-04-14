<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use Illuminate\Http\Request;
use App\Http\Resources\UserRoleResource as UserRoleResource;



class UserRoleController extends Controller
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
     *   path="/api/roles",
     *   summary="Get Auth User Role",
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
        $userRole = $request->user()->role()->first();
        $userRole = new UserRoleResource($userRole);
        return response()->json($userRole, 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/all-roles",
     *   summary="Get All Roles",
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

    public function roles()
    {
        $roles = UserRoleResource::collection(UserRole::all());

        return response()->json($roles, 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/roles/{id}",
     *   summary="Get Specific Role",
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
        $role = UserRole::where('id', $id)->first();

        if (!$role) {
            return response()->json($this->error, 404);
        }

        $role = new UserRoleResource($role);

        return response()->json($role, 200);

    }

    /**
     * @SWG\Post(
     *   path="/api/roles",
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
     *     name="acl",
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
        $v = validator($request->only(
            'name',
            'acl'
        ), [
            'name' => 'required|string|max:255',
            'acl'  => 'required|string|max:255'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = $request->only('name', 'acl');

        $role = UserRole::create([
            'name' => $data['name'],
            'acl'  => $data['acl']
        ]);

        $role = new UserRoleResource($role);

        return response()->json($role, 200);
    }

    /**
     * @SWG\Put(
     *   path="/api/roles/{id}",
     *   summary="Update role",
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
     *     name="acl",
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
        $role = UserRole::where('id', $id)->first();

        if (!$role) {
            return response()->json($this->error, 404);
        }

        $input = $request->all();
        $role->fill($input)->save();

        $role = new UserRoleResource($role);
        return response()->json($role, 200);
    }

    /**
     * @SWG\Delete(
     *   path="/api/roles/{id}",
     *   summary="Delete role",
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
        $role = UserRole::where('id', $id)->first();

        if (!$role) {
            return response()->json($this->error, 404);
        }

        $role->delete();
        return response()->json([], 200);
    }
}

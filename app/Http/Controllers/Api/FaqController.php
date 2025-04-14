<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Http\Resources\FaqResource;


class FaqController extends Controller
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
     *   path="/api/faq",
     *   summary="Get FAQ",
     *   tags={"FAQ"},
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
        $faqs = FaqResource::collection(Faq::orderBy('order')->get());
        return response()->json($faqs, 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/faq/{id}",
     *   summary="Get Specific FAQ",
     *   tags={"FAQ"},
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
        $faq = Faq::where('id', $id)->first();

        if (!$faq) {
            return response()->json($this->error, 404);
        }

        $faq = new FaqResource($faq);

        return response()->json($faq, 200);
    }

    /**
     * @SWG\Post(
     *   path="/api/faq",
     *   summary="Store faq",
     *   tags={"FAQ"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="question",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="answer",
     *     in="formData",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="order",
     *     in="formData",
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

    public function store(Request $request)
    {
        $v = validator($request->only(
            'question',
            'answer',
            'order'
        ), [
            'question' => 'required|string|max:255',
            'answer'  => 'required|string|max:255',
            'order'     => 'required|integer',
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = $request->only(
            'question',
            'answer',
            'order');

        $faq = Faq::create([
            'question'  => $data['question'],
            'answer'    => $data['answer'],
            'order'     => $data['order']
        ]);

        $faq = new FaqResource($faq);

        return response()->json($faq, 200);
    }

    /**
     * @SWG\Put(
     *   path="/api/faq/{id}",
     *   summary="Update faq",
     *   tags={"FAQ"},
     *   security={
     *         {"oauth2_security":{}}
     *     },
     *   @SWG\Parameter(
     *     name="question",
     *     in="formData",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     type="number"
     *   ),
     *   @SWG\Parameter(
     *     name="answer",
     *     in="formData",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="order",
     *     in="formData",
     *     required=false,
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

    public function update(Request $request, $id)
    {
        $faq = Faq::where('id', $id)->first();

        if (!$faq) {
            return response()->json($this->error, 404);
        }

        $input = $request->all();
        $faq->fill($input)->save();

        $faq = new FaqResource($faq);

        return response()->json($faq, 200);
    }

    /**
     * @SWG\Delete(
     *   path="/api/faq/{id}",
     *   summary="Delete faq",
     *   tags={"FAQ"},
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
        $faq = Faq::where('id', $id)->first();

        if (!$faq) {
            return response()->json($this->error, 404);
        }

        $faq->delete();

        return response()->json([], 200);
    }
}
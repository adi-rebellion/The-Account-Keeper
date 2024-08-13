<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Repositories\AuthRepository;
use Exception;
use Auth;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{

    use ResponseTrait;
    public $authAccess;

    public function __construct(AuthRepository $authAccess)
    {
        $this->authAccess = $authAccess;
    }
/**
 * @OA\Post(
 *     path="/api/login",
 *     summary="User Login",
 *     description="API endpoint to login an existing user with email and password returning a success message or error.",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 required={"email","password"},
 *                 @OA\Property(property="email", type="string", example="mohans@example.com",description="User's registered email address."),
 *                 @OA\Property(property="password", type="string", example="password123",description="User's password for account authentication.")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Logged in successfully.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Logged in successfully."),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="user", type="object",
 *                     @OA\Property(property="id", type="integer", example=3),
 *                     @OA\Property(property="name", type="string", example="Mohan Sharma"),
 *                     @OA\Property(property="email", type="string", example="mohans@example.com"),
 *                     @OA\Property(property="email_verified_at", type="string", nullable=true, example=null),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-04T19:52:26.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-07-04T19:52:26.000000Z")
 *                 ),
 *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
 *                 @OA\Property(property="token_type", type="string", example="Bearer"),
 *                 @OA\Property(property="expires_at", type="string", example="2025-07-04 19:52:53")
 *             ),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Sorry, something went wrong. Please try again."),
 *             @OA\Property(property="data", type="object", nullable=true, example=null),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     )
 * )
 */

    public function login(LoginRequest $request):JsonResponse
    {
        try{
           $authenticatedUserData = $this->authAccess->loginUser($request->all());
           return $this->responseSuccess($authenticatedUserData,'Logged in successfully.');
        }catch(Exception $e){
            return $this->responseError([],$e->getMessage());
        }

    }



/**
 * @OA\Post(
 *     path="/api/logout",
 *     summary="User Logout",
 *     description="API endpoint to logout an Auth user returning a success message or error.",
 *     tags={"Authentication"},
 *     security={{"bearer": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="User logged out successfully.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User logged out successfully."),
 *             @OA\Property(property="data", type="string", example=""),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="data", type="object", nullable=true, example=null),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Sorry, something went wrong. Please try again."),
 *             @OA\Property(property="data", type="object", nullable=true, example=null),
 *             @OA\Property(property="errors", type="object", nullable=true, example=null)
 *         )
 *     )
 * )
 */

    public function logout():JsonResponse
    {
        try{
             Auth::guard()->user()->token()->revoke();
             Auth::guard()->user()->token()->delete();
            return $this->responseSuccess('','User logged out successfully.');
        }catch(Exception $e){
            return $this->responseError([],$e->getMessage());
        }

    }

    public function profile():JsonResponse
    {
        try{
           $loggedINuserData = Auth::guard()->user();
           return $this->responseSuccess($loggedINuserData,'Hello '.$loggedINuserData->name);
        }catch(Exception $e){
            return $this->responseError([],$e->getMessage());
        }

    }


}

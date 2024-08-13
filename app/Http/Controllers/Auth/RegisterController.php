<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Repositories\AuthRepository;
use Exception;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{

    use ResponseTrait;
    public $authAccess;

    public function __construct(AuthRepository $authAccess)
    {
        $this->authAccess = $authAccess;
    }

/**
 * @OA\Post(
 *     path="/api/register",
 *     summary="User Register",
 *     description="API endpoint to register a new user with fields for name, email, password, and confirmation, returning a   success message or error.",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 required={"name","email","password","password_confirmation"},
 *                 @OA\Property(property="name", type="string", example="Mohan Sharma",description="User's full name"),
 *                 @OA\Property(property="email", type="string", example="mohans@example.com",description="User's email address for registration."),
 *                 @OA\Property(property="password", type="string", example="password123",description="User's chosen password for account access."),
 *                 @OA\Property(property="password_confirmation", type="string", example="password123",description="Re-enter the password to confirm accuracy.")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User registered successfully.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User registered successfully."),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="user", type="object",
 *                     @OA\Property(property="name", type="string", example="Mohan Sharma"),
 *                     @OA\Property(property="email", type="string", example="mohans@example.com"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-07-04T16:09:33.000000Z"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-04T16:09:33.000000Z"),
 *                     @OA\Property(property="id", type="integer", example=6)
 *                 ),
 *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
 *                 @OA\Property(property="token_type", type="string", example="Bearer"),
 *                 @OA\Property(property="expires_at", type="string", example="2025-07-04 16:09:33")
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


    public function register(RegisterRequest $request):JsonResponse
    {
        try{
           $registeredUserData = $this->authAccess->registerUser($request->all());
           return $this->responseSuccess($registeredUserData,'User registered successfully.');
        }catch(Exception $e){
            return $this->responseError([],$e->getMessage());
        }

    }
}




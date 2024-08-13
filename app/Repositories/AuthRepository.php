<?php

namespace App\Repositories;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use \Laravel\Passport\PersonalAccessTokenResult;

class AuthRepository
{
    public function loginUser(array $recievedUserInput) :array
    {
        $user = $this->getUserByEmail($recievedUserInput['email']);
        if(!$user)
        {
            throw new Exception("Sorry, user does not exist.",404);
        }

        if(!($this->isValidPassword($user, $recievedUserInput['password'])))
        {
            throw new Exception("Sorry, email or password does not match.",401);
        }

        $authTokenData = $this->createAuthToken($user);
        return $this->createAuthData($user, $authTokenData);


    }

    public function getUserByEmail(string $email): ?User
    {
        return  User::where('email',$email)->first();
    }

    public function isValidPassword(User $user, string $password): bool
    {
        return Hash::check($password,$user->password);
    }

    public function createAuthToken(User $user): PersonalAccessTokenResult
    {
        return $user->createToken('authToken');
    }

    public function createAuthData(User $user,PersonalAccessTokenResult $tokenObj) :array
    {
        return [
            'user' => $user,
            'access_token' => $tokenObj->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenObj->token->expires_at)->toDateTimeString()
        ];
    }

    public function registerUser(array $recievedUserInput) :array
    {
        $user = User::create($this->prepareDataForRegister($recievedUserInput));
        if(!$user)
        {
            throw new Exception("Sorry, something went wrong. Please try again.",500);
        }

        $authTokenData = $this->createAuthToken($user);
        return $this->createAuthData($user, $authTokenData);


    }

    public function prepareDataForRegister(array $recievedUserInput): array {

        return [
            'name' => $recievedUserInput['name'],
            'email' => $recievedUserInput['email'],
            'password' => Hash::make($recievedUserInput['password'])
        ];
    }


}

<?php
namespace App\Utilities;

use Tymon\JWTAuth\Facades\JWTAuth;

class AuthUtility
{
    /**
     * @param $email
     * @param $password
     * @param int $banned
     * @param bool $email_verified_at
     * @return mixed
     */
    public  static function createTokenNormal($email, $password, int $banned=0,bool $email_verified_at = true)
    {
        $user = \App\Models\User::where(["email"=>$email,"banned"=>$banned]);
        if ($email_verified_at){
            $user->where("email_verified_at","!=",null);
        }
        if ($user->count() > 0){
            return app('auth')->setTTL(10080)->attempt(["email"=>$email,"password"=>$password,"banned"=>$banned]);
        }
        return false;

    }

    /**
     * @param $email
     * @param false $banned
     * @param bool $email_verified_at
     * @return mixed
     */
    public static function createTokenFromEmail($email, int $banned=0,bool $email_verified_at = true)
    {
        $user = \App\Models\User::where(["email"=>$email,"banned"=>$banned]);
        if ($email_verified_at){
            $user->where("email_verified_at","!=",null);
        }
        if ($user->count() > 0){
            $token = JWTAuth::fromUser($user->first());
        }else{
            $token = false;
        }

        return $token;
    }
}

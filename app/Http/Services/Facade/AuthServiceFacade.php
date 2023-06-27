<?php

namespace App\Http\Services\Facade;

/**
 * @method static object|array|null loginLMS($email, $password)
 * @method static object|array|null loginLMSAndSyncLocalUser($email, $password)
 *
 * @see \App\Http\Services\AuthService
 */
class AuthServiceFacade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'auth-service';
    }
}

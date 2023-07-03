<?php
/**
 * Created by PhpStorm
 * Filename: ClassService.php
 * User: quanph
 * Date: 27/06/2023
 * Time: 08:55
 */

namespace App\Http\Services;

use App\Models\User;

class AuthService
{
    public function loginLMS($email, $password): object|array|null
    {
        $loginResponse = APIService::login($email, $password);
        if (isset($loginResponse->error)) {
            return (object) [
                'error' => $loginResponse->error->message,
            ];
        }

        $accountInfoResponse = APIService::getAccountInfo($loginResponse->idToken);
        $accountId           = json_decode($accountInfoResponse->users[0]->customAttributes)->id;
        $teacherInfoResponse = APIService::findInfoInRoleById($loginResponse->idToken, $accountId);
        $loginResponse->info = $teacherInfoResponse->data->users->findInfoInRoleById[0]->info;

        return $loginResponse;
    }

    public function loginLMSAndSyncLocalUser($email, $password): User|array|null
    {
        $loginResponse = $this->loginLMS($email, $password);
        if (isset($loginResponse->error)) {
            return $loginResponse;
        }

        return User::updateOrCreate([
            'email' => $email,
        ], [
            'name'     => $loginResponse->displayName,
            'password' => $password,
            'token'    => $loginResponse->idToken,
            'info'     => json_encode($loginResponse),
        ]);
    }
}

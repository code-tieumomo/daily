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
use LMSAuthService;

class ClassService
{
    public function getClassesAndKeepUserAlive(User &$user, $page = 1, $number = 10): object|array|null
    {
        $classes = APIService::getClasses($user->token, $user->info?->info?->_id, $page, $number);
        if (isset($classes->errors)) {
            $user    = LMSAuthService::loginLMSAndSyncLocalUser($user->email, $user->password);
            $classes = APIService::getClasses($user->token, $user->info?->info?->_id, $page, $number);
        }

        return $classes;
    }
}

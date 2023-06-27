<?php

namespace App\Http\Services;

use App\Models\User;
use LMSAuthService;

class TeacherService
{
    public function getTeachersAndKeepUserAlive(User $user, $page = 1, $number = 10): object|array|null
    {
        $teachers = APIService::getTeachers($user->token, $page, $number);
        if (isset($teachers->errors)) {
            $user     = LMSAuthService::loginLMSAndSyncLocalUser($user->email, $user->password);
            $teachers = APIService::getTeachers($user->token, $page, $number);
        }

        return $teachers;
    }
}

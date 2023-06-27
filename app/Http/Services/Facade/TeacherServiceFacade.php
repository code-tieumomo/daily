<?php
/**
 * Created by PhpStorm
 * Filename: TeacherServiceFacade.php
 * User: quanph
 * Date: 23/06/2023
 * Time: 01:47
 */

namespace App\Http\Services\Facade;

use App\Models\User;
use Illuminate\Support\Facades\Facade;

/**
 * @method static object|array|null getTeachersAndKeepUserAlive(User $user, $page = 1, $number = 10)
 *
 * @see \App\Http\Services\TeacherService
 */
class TeacherServiceFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'teacher-service';
    }
}

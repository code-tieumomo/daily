<?php
/**
 * Created by PhpStorm
 * Filename: ClassServiceFacade.php
 * User: quanph
 * Date: 27/06/2023
 * Time: 08:56
 */

namespace App\Http\Services\Facade;

use App\Models\User;
use Illuminate\Support\Facades\Facade;

/**
 * @method static object|array|null getClassesAndKeepUserAlive(User $user, $page = 1, $number = 10)
 *
 * @see \App\Http\Services\ClassService
 */
class ClassServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'class-service';
    }
}

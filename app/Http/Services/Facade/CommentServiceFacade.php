<?php
/**
 * Created by PhpStorm
 * Filename: CommentServiceFacade.php
 * User: quanph
 * Date: 27/06/2023
 * Time: 16:43
 */

namespace App\Http\Services\Facade;

use App\Http\Services\CommentService;
use App\Models\User;

/**
 * @method static setReviewAndKeepUserAlive(User $user, $classId, $slotId, $studentId, $studentAttendanceId, $comment)
 *
 * @see CommentService
 */
class CommentServiceFacade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'comment-service';
    }
}

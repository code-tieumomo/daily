<?php
/**
 * Created by PhpStorm
 * Filename: ReviewService.php
 * User: quanph
 * Date: 27/06/2023
 * Time: 09:43
 */

namespace App\Http\Services;

use App\Models\User;

class CommentService
{
    public function setReviewAndKeepUserAlive(
        User $user,
        $classId,
        $slotId,
        $studentId,
        $studentAttendanceId,
        $comment
    ): ?object {
        $commented = APIService::setReview($user->token, $classId, $slotId, $studentId, $studentAttendanceId, $comment);
        if (isset($commented->errors)) {
            $user      = AuthService::loginLMSAndSyncLocalUser($user->email, $user->password);
            $commented = APIService::setReview(
                $user->token, $classId, $slotId, $studentId, $studentAttendanceId, $comment
            );
        }

        return $commented;
    }
}

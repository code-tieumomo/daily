<?php

namespace App\Http\Controllers;

use App\Http\Services\APIService;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Laravel\Firebase\Facades\Firebase;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse|bool|string
    {
        $email    = $request->email;
        $password = $request->password;

        if (config('auth.mindx_auth')) {
            $loginResponse = APIService::login($email, $password);
            if (isset($loginResponse->error)) {
                return response()->json([
                    'error' => $loginResponse->error->message,
                ], 404);
            }

            $accountInfoResponse = APIService::getAccountInfo($loginResponse->idToken);
            $accountId           = json_decode($accountInfoResponse->users[0]->customAttributes)->id;
            $teacherInfoResponse = APIService::findInfoInRoleById($loginResponse->idToken, $accountId);
            $loginResponse->info = $teacherInfoResponse->data->users->findInfoInRoleById[0]->info;

            $user = User::updateOrCreate([
                'email' => $email,
            ], [
                'name'     => $loginResponse->displayName,
                'password' => $password,
                'token'    => $loginResponse->idToken,
                'info'     => json_encode($loginResponse),
            ]);
        } else {
            $user = User::where('email', $email)->first();
            if (!$user || $password != $user->password) {
                return response()->json([
                    'error' => 'Email hoặc mật khẩu không chính xác',
                ], 404);
            }
        }

        Auth::login($user);

        return response()->json(Auth::user());
    }

    public function user()
    {
        return response()->json(Auth::user());
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Session::regenerate();

        return response()->json([
            'message' => 'Đăng xuất thành công',
        ]);
    }

    public function authorizeTeacherFromDCSR(Request $request)
    {
        try {
            $payload = json_decode($request->getContent(), true);
            $uid     = Arr::get($payload, 'uid', '');
            $email   = Arr::get($payload, 'email', '');

            $isTeacher = Teacher::where('email', $email)->exists();
            if ($isTeacher) {
                $ref = Firebase::database()->getReference('users/' . $uid);
                $ref->set([
                    'is_teacher' => true,
                ]);
                Teacher::where('email', $email)->update([
                    'firebase_id' => $uid,
                ]);

                return response()->json([
                    'message' => 'Đã cấp quyền xem tài liệu thành công',
                ]);
            }

            return response()->json([
                'error' => 'Không tìm thấy giảng viên trong hệ thống',
            ], 500);
        } catch (DatabaseException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

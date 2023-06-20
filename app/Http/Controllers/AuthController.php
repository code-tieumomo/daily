<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Services\APIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse|bool|string
    {
        $email = $request->email;
        $password = $request->password;

        if (config('auth.mindx_auth')) {
            $loginResponse = APIService::login($email, $password);
            if (isset($loginResponse->error)) {
                return response()->json([
                    'error' => $loginResponse->error->message,
                ], 404);
            }

            $accountInfoResponse = APIService::getAccountInfo($loginResponse->idToken);
            $accountId = json_decode($accountInfoResponse->users[0]->customAttributes)->id;
            $teacherInfoResponse = APIService::findInfoInRoleById($loginResponse->idToken, $accountId);
            $loginResponse->info = $teacherInfoResponse->data->users->findInfoInRoleById[0]->info;

            $user = User::updateOrCreate([
                'email' => $email,
            ], [
                'name' => $loginResponse->displayName,
                'password' => $password,
                'token' => $loginResponse->idToken,
                'info' => json_encode($loginResponse)
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
            'message' => 'Successfully logged out'
        ]);
    }
}

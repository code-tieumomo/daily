<?php

namespace App\Http\Controllers;

use App\Http\Services\APIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->page;
        $number = $request->number;
        $teacherId = $request->teacherId;

        $classes = Cache::remember('classes-' . Auth::id(), 600, function () use ($number, $page, $teacherId) {
            return APIService::getClasses(Auth::user()->token, $teacherId, $page, $number);
        });
        if (isset($classes->errors)) {
            return response()->json([
                'error' => $classes->errors[0]->message,
            ], 404);
        }

        return response()->json($classes->data->classes->data);
    }

    public function show(Request $request, $id)
    {
        $class = APIService::getClassById(Auth::user()->token, $id);
        if (isset($class->errors)) {
            return response()->json([
                'error' => $class->errors[0]->message,
            ], 404);
        }

        return response()->json($class->data->classesById);
    }
}

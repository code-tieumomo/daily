<?php

namespace App\Http\Controllers;

use App\Contracts\APIResponse\APIResponse;
use App\Enums\HTTPResponseCode;
use App\Http\Services\APIService;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LMSClassService;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $page    = (int) $request->page;
        $number  = (int) $request->number;
        $classes = Cache::remember('classes-' . Auth::id(), 300, function () use ($page, $number) {
            return LMSClassService::getClassesAndKeepUserAlive(Auth::user(), $page, $number);
        });

        if (isset($classes->errors)) {
            return response()->json([
                'error' => $classes->errors[0]->message,
            ], $classes->errors[0]->extensions->code == 'INVALID_TOKEN' ? 419 : 404);
        }

        return response()->json($classes->data->classes->data);
    }

    public function show($id)
    {
        $class = APIService::getClassById(Auth::user()->token, $id);
        if (isset($class->errors)) {
            return response()->json([
                'error' => $class->errors[0]->message,
            ], $class->errors[0]->extensions->code == 'INVALID_TOKEN' ? 419 : 404);
        }

        $class = $class->data->classesById;
        foreach ($class->slots as $slotIdx => $slot) {
            $startTime = Carbon::parse($slot->startTime);
            $now       = Carbon::create(2023, 6, 27, 12); // Carbon::now();
            if ($now->diffInHours($startTime, false) < 0) {
                $class->slots[$slotIdx]->status = 'FINISHED';
            } else {
                if ($now->diffInHours($startTime) <= 2) {
                    $class->slots[$slotIdx]->status = 'IN_CLASS';
                    $class->currentSlot             = $slotIdx;
                } else {
                    $class->slots[$slotIdx]->status = 'UPCOMING';
                }
            }
        }

        return response()->json($class);
    }

    public function inClass()
    {
        $criteria                       = config('lms.classes_criteria');
        $inClassDiff                    = config('lms.in_class_diff');
        $now                            = Carbon::create(2023, 6, 27, 12); // Carbon::now();
        $twoHoursBefore                 = $now->subHours(2)->subSecond($inClassDiff)->format('c');
        $twoHoursAfter                  = $now->addHours(4)->addSecond(2 * $inClassDiff)->format('c');
        $criteria['haveSlotIn']['from'] = $twoHoursBefore;
        $criteria['haveSlotIn']['to']   = $twoHoursAfter;

        $classes = LMSClassService::getClassesAndKeepUserAlive(Auth::user(), 1, 1000, $criteria);
        if (isset($classes->errors)) {
            return response()->json(
                APIResponse::error($classes->errors[0]->message),
                HTTPResponseCode::INTERNAL_SERVER_ERROR
            );
        }

        $classes = $classes->data->classes->data;
        $classes = array_map(function ($class) {
            foreach ($class->slots as $slotIdx => $slot) {
                $startTime = Carbon::parse($slot->startTime);
                $now       = Carbon::create(2023, 6, 27, 12); // Carbon::now();
                if ($now->diffInHours($startTime) <= 2) {
                    $class->currentSlot = $slotIdx;
                }
            }

            return [
                'id'          => $class->id,
                'name'        => $class->name,
                'currentSlot' => $class->currentSlot,
                'slots'       => [
                    $class->currentSlot => $class->slots[$class->currentSlot],
                ],
                'course'      => [
                    'shortName' => $class->course->shortName,
                ],
            ];
        }, $classes);

        return response()->json(APIResponse::success($classes));
    }
}

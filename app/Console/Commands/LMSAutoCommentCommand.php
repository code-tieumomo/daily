<?php

namespace App\Console\Commands;

use App\Models\SampleComment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LMSAuthService;
use LMSClassService;
use LMSCommentService;

class LMSAutoCommentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lms:auto-comment {--email=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto fill comment for LMS';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $email    = $this->option('email');
        $password = $this->option('password');
        if (is_null($email)) {
            $this->error('Email is required');
            return self::FAILURE;
        }

        if (is_null($password)) {
            $temp = User::where('email', $email)->first();
        }

        $user = isset($temp)
            ? LMSAuthService::loginLMSAndSyncLocalUser($temp->email, $temp->password)
            : LMSAuthService::loginLMSAndSyncLocalUser($email, $password);
        if (!($user instanceof User)) {
            $this->error('User not found');
            return self::FAILURE;
        }

        $channel = Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/auto-comment/user-' . $user->id . '-' . Carbon::now()->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d-H-i-s') . '.log'),
        ]);

        $classes = LMSClassService::getClassesAndKeepUserAlive($user, 1, 1000);
        if (empty($classes->data?->classes?->data)) {
            $this->error('Cannot get classes');
            return self::FAILURE;
        }

        $classes = collect($classes->data->classes->data);
        $classes->each(function ($class) use ($user, $channel) {
            Log::stack([$channel])->info(' -- ' . $class->name . ' -- ' . $class->status . PHP_EOL . PHP_EOL . ' DONE');
            $this->info(' -- ' . $class->name . ' -- ' . $class->status);

            foreach ($class->slots as $i => $slot) {
                $endTime = Carbon::parse($slot->endTime)->timezone('Asia/Ho_Chi_Minh');
                if ($endTime->isPast()) {
                    $studentsFiltered = collect($slot->studentAttendance)->filter(function ($student) {
                        if (
                            in_array($student->status, ['LATE_ARRIVED', 'ATTENDED'])
                            && trim($student->comment) === ''
                        ) {
                            return true;
                        }

                        return false;
                    });

                    if ($studentsFiltered->count() > 0) {
                        Log::stack([$channel])->log(
                            $endTime->isToday() ? 'warning' : 'emergency',
                            'Lesson ' . ($i + 1) . ' needs to be commented'
                        );

                        $studentsFiltered->map(function ($student) use ($class, $slot, $user, $endTime, $channel) {
                            Log::stack([$channel])->log(
                                $endTime->isToday() ? 'warning' : 'emergency',
                                'Student ' . $student->student->fullName . ' needs to be commented'
                            );

                            $commented = LMSCommentService::setReviewAndKeepUserAlive(
                                $user->token,
                                $class->id,
                                $slot->_id,
                                $student->student->id,
                                $student->_id,
                                SampleComment::inRandomOrder()->first()->comment
                            );

                            if (isset($commented->errors)) {
                                Log::stack([$channel])->error('Comment failed', $commented->errors);
                            } else {
                                Log::stack([$channel])->info('Commented', json_decode(json_encode($commented), true));
                            }
                        });
                    }
                }
            }
        });

        return self::SUCCESS;
    }
}

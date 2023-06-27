<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LMSAuthService;
use LMSClassService;

class LMSAutoReviewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lms:auto-review {--email=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto fill review for LMS';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->option('email');
        if (is_null($email)) {
            $this->error('Email is required');
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        $user = LMSAuthService::loginLMSAndSyncLocalUser($user->email, $user->password);
        if (!($user instanceof User)) {
            $this->error('User not found');
            return self::FAILURE;
        }

        $channel = Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/auto-review/user-' . $user->id . '-' . Carbon::now()->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d-H-i-s') . '.log'),
        ]);

        Log::stack([$channel])->info('Start auto review for user ' . $user->email);

        $classes = LMSClassService::getClassesAndKeepUserAlive($user, 1, 100);
        if (empty($classes->data?->classes?->data)) {
            $this->error('Cannot get classes');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

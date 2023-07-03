<?php

namespace App\Console\Commands;

use App\Models\SampleComment;
use App\Models\User;
use Illuminate\Console\Command;
use LMSAuthService;
use LMSClassService;

class LMSCrawlCommentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lms:crawl-comments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl comment from LMS';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $user = User::first();
        $user = LMSAuthService::loginLMSAndSyncLocalUser($user->email, $user->password);
        $this->info('Start crawl comments for user ' . $user->email);

        $classes = LMSClassService::getClassesAndKeepUserAlive($user, 1, 1000);
        collect($classes->data->classes->data)->each(function ($class) {
            $this->info('Start crawl comments for class ' . $class->name);
            collect($class->slots)->each(function ($slot, $i) use ($class) {
                $this->info('Start crawl comments for lesson ' . ($i + 1));
                collect($slot->studentAttendance)->each(function ($student) use ($slot, $class) {
                    if (trim($student->comment) !== '') {
                        SampleComment::updateOrCreate(
                            ['slot_id' => $slot->_id],
                            ['comment' => $student->comment]
                        );
                    }
                });
            });
        });

        return self::SUCCESS;
    }
}

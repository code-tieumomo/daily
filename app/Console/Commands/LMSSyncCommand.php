<?php

namespace App\Console\Commands;

use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use LMSAuthService;
use LMSTeacherService;
use Symfony\Component\Console\Command\Command as CommandAlias;
use function Termwind\render;

class LMSSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lms:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ tài nguyên từ LMS về hệ thống';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        render('<div class="mb-1 px-1 bg-emerald-500 text-black font-bold">Start sync</div>');

        $user       = User::first();
        $loggedUser = LMSAuthService::loginLMSAndSyncLocalUser($user->email, $user->password);
        if (isset($loggedUser->error)) {
            render('<div class="mb-1 px-1 bg-red-500 text-black font-bold">Sync failed! Error:' . $loggedUser->error . '</div>');

            return CommandAlias::FAILURE;
        }

        $this->syncTeachers($loggedUser);

        return CommandAlias::SUCCESS;
    }

    private function syncTeachers($user): void
    {
        $this->info('Start sync teachers and users using account ' . $user->email);

        $page  = 1;
        $count = 0;

        $bar = $this->output->createProgressBar();
        $bar->start();
        do {
            $teachers = LMSTeacherService::getTeachersAndKeepUserAlive($user, $page, 30);
            $total    = $teachers->data->teachers->pagination->total;
            $bar->setMaxSteps($total);

            foreach ($teachers->data->teachers->data as $teacher) {
                Teacher::updateOrCreate(
                    ['lms_id' => $teacher->id],
                    [
                        'lms_id'          => $teacher->id,
                        'username'        => $teacher->username,
                        'user_id'         => 1,
                        'firebase_id'     => $teacher->firebaseId,
                        'full_name'       => $teacher->fullName,
                        'code'            => $teacher->code,
                        'phone_number'    => $teacher->phoneNumber,
                        'email'           => $teacher->email,
                        'gender'          => $teacher->gender,
                        'dob'             => is_null($teacher->dob) ? null : Carbon::parse($teacher->dob)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
                        'image_url'       => $teacher->imageUrl,
                        'address'         => $teacher->address,
                        'facebook'        => $teacher->facebook,
                        'notes'           => $teacher->notes,
                        'is_active'       => $teacher->isActive,
                        'created_at'      => is_null($teacher->createdAt) ? null : Carbon::createFromTimestampMs($teacher->createdAt)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
                        'created_by'      => $teacher->createdBy,
                        'updated_at'      => is_null($teacher->lastModifiedAt) ? null : Carbon::createFromTimestampMs($teacher->lastModifiedAt)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s'),
                        'last_updated_by' => $teacher->lastModifiedBy,
                    ]
                );

                User::updateOrCreate(
                    [
                        'email' => $teacher->email,
                    ],
                    [
                        'lms_id' => $teacher->id,
                        'name'   => $teacher->fullName,
                    ]
                );

                $count++;
                $bar->advance();
            }
            $page++;
        } while ($count < $total);
        $bar->finish();
    }
}

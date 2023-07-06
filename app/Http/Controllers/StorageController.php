<?php

namespace App\Http\Controllers;

use App\Contracts\APIResponse\APIResponse;
use App\Http\Services\APIService;
use Carbon\Carbon;
use Exception;
use Google\Client;
use Google\Service\Docs;
use Google\Service\Docs\BatchUpdateDocumentRequest;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StorageController extends Controller
{
    public function index()
    {
        try {
            $client = new Client();
            $client->setApplicationName('daily');
            $client->setScopes([Drive::DRIVE]);
            $client->setAccessType('offline');
            $client->setAuthConfig(storage_path('app/google/credentials.json'));
            $driveService = new Drive($client);

            $pageToken = null;
            do {
                $response = $driveService->files->listFiles(array(
                    'q'         => "'root' in parents",
                    'spaces'    => 'drive',
                    'pageToken' => $pageToken,
                    'fields'    => 'nextPageToken, files(id, name, mimeType, hasThumbnail, thumbnailLink, webViewLink, webContentLink, iconLink, size, modifiedTime)',
                ));
                $files[]  = $response->files;

                $pageToken = $response->pageToken;
            } while ($pageToken != null);

            return response()->json(APIResponse::success($files));
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createCheckpoint(Request $request, int $number = 1)
    {
        $channel = Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/auto-checkpoint/user-' . Auth::id() . '-' . Carbon::now()->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d-H-i-s') . '.log'),
        ]);

        try {
            $classId    = $request->input('classId');
            $class      = APIService::getClassById(Auth::user()->token, $classId)->data->classesById;
            $course     = $class->course->id;
            $courseData = config('courses.' . $course);
            $cpCode     = 'cp' . $number;

            $client = new Client();
            $client->setApplicationName('daily');
            $client->setScopes([Drive::DRIVE]);
            $client->setAccessType('offline');
            $client->setAuthConfig(storage_path('app/google/credentials.json'));
            $driveService = new Drive($client);
            $docsService  = new Docs($client);

            $postBody = new DriveFile([
                'name'     => $class->name . '-' . strtoupper($cpCode) . '-' . Carbon::now()->getTimestamp(),
                'mimeType' => 'application/vnd.google-apps.folder',
            ]);

            $folder     = $driveService->files->create($postBody);
            $resultLink = 'https://drive.google.com/drive/folders/' . $folder->id;

            $newPermission = new Permission([
                'type'         => 'user',
                'role'         => 'writer',
                'emailAddress' => 'code.tieumomo@gmail.com',
            ]);
            $permission    = $driveService->permissions->create(
                $folder->id,
                $newPermission
            );

            foreach ($class->students as $student) {
                if ($student->activeInClass) {
                    $documentId       = config('lms.docs_sample_checkpoint_id');
                    $copyTitle        = 'Checkpoint ' . $number . ' [' . ucwords(strtolower($student->student->fullName)) . '] [' . $class->name . ']';
                    $copy             = new DriveFile([
                        'name' => $copyTitle,
                    ]);
                    $documentCopied   = $driveService->files->copy($documentId, $copy);
                    $documentCopiedId = $documentCopied->id;

                    $emptyFileMetadata = new DriveFile();
                    $file              = $driveService->files->get($documentCopiedId, array('fields' => 'parents'));
                    $previousParents   = join(',', $file->parents);

                    $file = $driveService->files->update($documentCopiedId, $emptyFileMetadata, [
                        'addParents'    => $folder->id,
                        'removeParents' => $previousParents,
                        'fields'        => 'id, parents',
                    ]);

                    $curInfo                 = $courseData[$cpCode];
                    $curInfo['course_name']  = $courseData['name'];
                    $curInfo['student_name'] = $student->student->fullName;
                    foreach ($curInfo as $key => $value) {
                        $request = new Docs\Request([
                            'replaceAllText' => [
                                'containsText' => [
                                    'text'      => '{{' . $key . '}}',
                                    'matchCase' => true,
                                ],
                                'replaceText'  => $value,
                            ],
                        ]);
                        $batch   = new BatchUpdateDocumentRequest([
                            'requests' => [$request],
                        ]);
                        $docsService->documents->batchUpdate($documentCopiedId, $batch);
                    }

                    Log::stack([$channel])->info('DONE for student ' . $student->student->fullName);
                    sleep(5);
                }
            }

            return response()->json($permission);
        } catch (Exception $e) {
            Log::stack([$channel])->error("Opps! Something went wrong\n" . $e->getMessage());

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

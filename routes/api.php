<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', fn() => response()->json(['message' => '༼ つ ◕_◕ ༽つ']));

Route::name('auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::any('/authorize-teacher-from-dcsr',
        [AuthController::class, 'authorizeTeacherFromDCSR'])->name('authorize-teacher-from-dcsr');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::name('auth.')->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });

    Route::name('classes.')->group(function () {
        Route::get('/classes', [ClassController::class, 'index'])->name('index');
        Route::get('/classes/in-class', [ClassController::class, 'inClass'])->name('in-class');
        Route::get('/classes/{id}', [ClassController::class, 'show'])->name('show');
    });

    Route::name('storages.')->group(function () {
        Route::get('/storages', [StorageController::class, 'index'])->name('index');
        Route::post('/create-checkpoint-{number}',
            [StorageController::class, 'createCheckpoint'])->name('create_checkpoint');
    });
});

/*
|--------------------------------------------------------------------------
| Test
|--------------------------------------------------------------------------
*/
Route::get('test-google', function () {
    $client = new \Google\Client();
    $client->setApplicationName('daily');
    $client->setScopes([\Google\Service\Drive::DRIVE]);
    $client->setAccessType('offline');
    $client->setAuthConfig(storage_path('app/google/credentials.json'));
    $driveService = new Google\Service\Drive($client);

    $pageToken = null;
    do {
        $response = $driveService->files->listFiles(array(
            'q'         => "'root' in parents",
            'spaces'    => 'drive',
            'pageToken' => $pageToken,
            'fields'    => 'nextPageToken, files(id, name)',
        ));
        $files[]  = $response->files;

        $pageToken = $response->pageToken;
    } while ($pageToken != null);
    dd(collect($files[0])->map(function ($file) {
        return $file->name;
    }));

    // try {
    //     $postBody = new \Google\Service\Drive\DriveFile([
    //         'name'     => 'This is a test folder ' . date_create()->format('d-m-Y H:i:s'),
    //         'mimeType' => 'application/vnd.google-apps.folder',
    //     ]);
    //
    //     $result = $driveService->files->create($postBody);
    //     echo '<pre>';
    //     print_r('<a target="_blank" href="https://drive.google.com/drive/folders/' . $result->id . '">LINK</a>');
    //     echo '</pre>';
    //
    //     $newPermission = new \Google\Service\Drive\Permission([
    //         'type'         => 'user',
    //         'role'         => 'writer',
    //         'emailAddress' => 'code.tieumomo@gmail.com',
    //     ]);
    //
    //     $permission = $driveService->permissions->create(
    //         $result->id,
    //         $newPermission
    //     );
    // } catch (Exception $e) {
    //     dd($e->getMessage());
    // }
});

<?php

namespace App\Console\Commands\Drive;

use Exception;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Console\Command;

class RemoveAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drive:remove-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa tất cả file và folder trong Drive chung';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        do {
            try {
                $files = [];

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

                if (count($files[0]) > 0) {
                    foreach ($files[0] as $file) {
                        $driveService->files->delete($file->id);
                        $this->info('Đã xóa ' . $file->name);
                    }
                }
            } catch (Exception $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }
        } while (count($files[0]) > 0);

        return self::SUCCESS;
    }
}

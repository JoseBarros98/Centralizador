<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckGoogleConfig extends Command
{
    protected $signature = 'google:check-config';
    protected $description = 'Check Google Drive configuration';

    public function handle()
    {
        $this->info('=== Google Drive Configuration Check ===');
        
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect_uri');
        $driveFolderId = config('services.google.drive_folder_id');
        
        $this->info('Client ID: ' . ($clientId ?: 'NOT SET'));
        $this->info('Client Secret: ' . ($clientSecret ? 'CONFIGURED' : 'NOT SET'));
        $this->info('Redirect URI: ' . ($redirectUri ?: 'NOT SET'));
        $this->info('Drive Folder ID: ' . ($driveFolderId ?: 'NOT SET'));
        
        $this->info('=== Environment Variables Check ===');
        $this->info('ENV Client ID: ' . (env('GOOGLE_CLIENT_ID') ?: 'NOT SET'));
        $this->info('ENV Client Secret: ' . (env('GOOGLE_CLIENT_SECRET') ? 'CONFIGURED' : 'NOT SET'));
        $this->info('ENV Drive Folder ID: ' . (env('GOOGLE_DRIVE_FOLDER_ID') ?: 'NOT SET'));
        
        return 0;
    }
}
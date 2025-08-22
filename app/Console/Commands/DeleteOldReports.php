<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DeleteOldReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete reports older than 7 days from storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = Storage::disk('public')->files('reports');
        foreach ($files as $file) {
            $fullPath = storage_path('app/public/' . $file);
            if (file_exists($fullPath)) {
                $lastModified = Carbon::createFromTimestamp(filemtime($fullPath));
                if ($lastModified->lt(Carbon::now()->subDays(7))) {
                    Storage::disk('public')->delete($file);
                    $this->info("Deleted: $file");
                }
            }
        }
        $this->info('Old reports cleaned up successfully!');
    }
}

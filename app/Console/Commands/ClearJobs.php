<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearJobs extends Command
{
    protected $signature = 'glint:clear-jobs {--photos : Also clear job_photos}';
    protected $description = 'Delete all jobs (and optionally job photos) from the database';

    public function handle(): int
    {
        $photos = $this->option('photos');
        DB::beginTransaction();
        try {
            if ($photos && DB::getSchemaBuilder()->hasTable('job_photos')) {
                DB::table('job_photos')->delete();
                $this->info('Cleared job_photos table');
            }
            DB::table('jobs')->delete();
            $this->info('Cleared jobs table');
            DB::commit();
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed to clear jobs: '.$e->getMessage());
            return Command::FAILURE;
        }
    }
}


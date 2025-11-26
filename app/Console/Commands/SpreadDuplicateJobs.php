<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SpreadDuplicateJobs extends Command
{
    protected $signature = 'glint:spread-duplicate-jobs {tenant : Tenant slug or ID} {--interval=7 : Days between duplicates when searching for a new slot} {--dry-run}';

    protected $description = 'Push duplicate jobs for the same time slot onto future dates so they no longer collide.';

    public function handle(): int
    {
        $tenantArg = $this->argument('tenant');
        $tenant = Tenant::query()
            ->where('id', $tenantArg)
            ->orWhere('slug', $tenantArg)
            ->first();

        if (!$tenant) {
            $this->error("Tenant {$tenantArg} was not found.");
            return Command::FAILURE;
        }

        $interval = (int) $this->option('interval');
        if ($interval <= 0) {
            $interval = 7;
        }

        $jobs = Job::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('date')
            ->orderBy('eta_window')
            ->orderBy('id')
            ->get();

        if ($jobs->isEmpty()) {
            $this->warn('No jobs found for that tenant.');
            return Command::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $moved = 0;
        $skipped = 0;

        $occupied = [];
        foreach ($jobs as $job) {
            $date = $job->date ? $job->date->format('Y-m-d') : null;
            if (!$date) {
                $this->warn("Job {$job->id} has no date assigned; skipping.");
                $skipped++;
                continue;
            }

            $address = $this->addressKey($job);
            $slotKey = $this->slotKey($date, $job->eta_window, $address);

            if (!isset($occupied[$slotKey])) {
                $occupied[$slotKey] = $job->id;
                continue;
            }

            $newSlot = $this->findNextSlot($job, $interval, $occupied);
            if (!$newSlot) {
                $this->error("Could not move job {$job->id}; no future slot available.");
                $skipped++;
                continue;
            }

            [$newDate, $newWindow] = $newSlot;
            $newKey = $this->slotKey($newDate, $newWindow, $address);
            $occupied[$newKey] = $job->id;

            if ($dryRun) {
                $this->line("Would move job {$job->id} from {$date} {$job->eta_window} to {$newDate} {$newWindow}.");
                $moved++;
                continue;
            }

            $job->date = Carbon::parse($newDate);
            $job->eta_window = $newWindow;
            $job->save();
            $moved++;
        }

        $message = $dryRun
            ? "Would move {$moved} duplicates for tenant {$tenant->slug}."
            : "Moved {$moved} duplicate jobs for tenant {$tenant->slug}.";
        $this->info($message);

        if ($skipped) {
            $this->warn("Skipped {$skipped} jobs that could not be adjusted.");
        }

        return Command::SUCCESS;
    }

    protected function findNextSlot(Job $job, int $intervalDays, array $occupied): ?array
    {
        $address = $this->addressKey($job);
        $baseDate = $job->date ? $job->date->copy() : Carbon::today();

        $maxIterations = 26; // roughly half a year of weekly attempts
        $window = $job->eta_window;

        for ($i = 1; $i <= $maxIterations; $i++) {
            $candidateDate = $baseDate->copy()->addDays($intervalDays * $i);
            $dateString = $candidateDate->format('Y-m-d');
            $candidateKey = $this->slotKey($dateString, $window, $address);
            if (!isset($occupied[$candidateKey])) {
                return [$dateString, $window];
            }
        }

        // As a fallback, allow small time adjustments within the same day window
        $parsed = $this->parseWindow($job->eta_window);
        $duration = $parsed['duration'];
        $startMinutes = $parsed['start'];
        $buffer = 10;

        for ($i = 1; $i <= $maxIterations; $i++) {
            $candidateDate = $baseDate->copy()->addDays($intervalDays * $i);
            $dateString = $candidateDate->format('Y-m-d');
            $start = $startMinutes;
            while ($start < (19 * 60)) {
                $end = $start + $duration;
                $windowLabel = $this->minutesToWindow($start, $end);
                $candidateKey = $this->slotKey($dateString, $windowLabel, $address);
                if (!isset($occupied[$candidateKey])) {
                    return [$dateString, $windowLabel];
                }
                $start += $duration + $buffer;
            }
        }

        return null;
    }

    protected function parseWindow(?string $window): array
    {
        if (!$window || !str_contains($window, '-')) {
            return ['start' => 8 * 60, 'end' => 9 * 60, 'duration' => 60];
        }

        [$startRaw, $endRaw] = array_map('trim', explode('-', $window, 2));
        $start = $this->timeToMinutes($startRaw);
        $end = $this->timeToMinutes($endRaw);

        if ($end <= $start) {
            $end = $start + 60;
        }

        return ['start' => $start, 'end' => $end, 'duration' => $end - $start];
    }

    protected function timeToMinutes(string $value): int
    {
        try {
            [$hour, $minute] = array_map('intval', explode(':', $value));
            return $hour * 60 + $minute;
        } catch (\Throwable $e) {
            return 8 * 60;
        }
    }

    protected function minutesToWindow(int $start, int $end): string
    {
        return sprintf('%02d:%02d-%02d:%02d', intdiv($start, 60), $start % 60, intdiv($end, 60), $end % 60);
    }

    protected function addressKey(Job $job): string
    {
        $address = data_get($job->checklist_json, 'address_line1');
        if (!$address) {
            return (string) $job->id;
        }

        return Str::lower(trim($address));
    }

    protected function slotKey(string $date, string $window, string $address): string
    {
        return implode('|', [$date, trim($window), $address]);
    }
}

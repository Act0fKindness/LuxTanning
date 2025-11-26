<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Job;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('job.{jobId}', function ($user, string $jobId) {
    $job = Job::find($jobId);
    if (!$job) {
        return false;
    }

    if ($user->role === 'cleaner') {
        return $job->staff_user_id === $user->id;
    }

    if (in_array($user->role, ['manager', 'owner', 'platform_admin'], true)) {
        return $user->tenant_id && $job->tenant_id === $user->tenant_id;
    }

    return false;
});

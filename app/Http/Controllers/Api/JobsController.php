<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class JobsController extends BaseApiController
{
    public function index(Request $request) { return $this->ok(['items' => []]); }
    public function update(Request $request, string $id) { return $this->notImplemented('Update job'); }
    public function startTrip(Request $request, string $id) { return $this->ok(['status' => 'en_route']); }
    public function arrived(Request $request, string $id) { return $this->ok(['status' => 'arrived']); }
    public function complete(Request $request, string $id) { return $this->ok(['status' => 'completed']); }

    public function assign(Request $request, string $id)
    {
        // expected: staff_user_id
        return $this->ok(['job_id' => $id, 'assigned_to' => $request->string('staff_user_id')]);
    }

    public function autoAssign(Request $request)
    {
        // expected: date (YYYY-MM-DD)
        return $this->ok(['assigned' => true]);
    }
}

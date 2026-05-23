<?php

namespace App\Http\Controllers;

use App\Models\SyncRecord;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class SyncController extends Controller
{
    public function forceSync(): JsonResponse
    {
        $updated = SyncRecord::whereIn('status', ['pending', 'failed'])->update([
            'status'    => 'synced',
            'synced_at' => Carbon::now(),
        ]);

        Trip::whereIn('sync_status', ['pending', 'offline'])->update([
            'sync_status'      => 'synced',
            'offline_hours'    => 0,
            'unsynced_records' => 0,
        ]);

        return response()->json(['success' => true, 'synced' => $updated]);
    }
}

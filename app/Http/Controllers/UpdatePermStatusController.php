<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\UpdatePermStatus;

class UpdatePermStatusController extends Controller
{
    public function updateStatus()
    {
        UpdatePermStatus::dispatchSync();
        return response()->json(['message' => 'Job dispatched']);
    }

}

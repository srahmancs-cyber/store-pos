<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(
        string $actionType,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null
    ): void {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action_type' => $actionType,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => mb_substr($description, 0, 255),
            'ip_address' => Request::ip(),
        ]);
    }
}

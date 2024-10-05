<?php

namespace App\Services;

use App\Models\SystemLog;

class Log
{
    public function create()
    {
        $customId = new CustomIds();
        $log = [
            'log_id' => $customId->generateId(),
            'user_id' => auth()->user()->user_id,
            'activity' => url()->current(),
        ];

        SystemLog::create($log);

    }
}

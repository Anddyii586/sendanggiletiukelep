<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdminAuditLogService
{
    public function log(
        User $admin,
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        array $metadata = []
    ): AdminAuditLog {
        $request = request();

        return AdminAuditLog::create([
            'admin_id' => $admin->id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'metadata' => $metadata ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}

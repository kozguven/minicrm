<?php

namespace App\Services\Audit;

use App\Models\AuditLog;

class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function log(
        ?int $userId,
        string $entityType,
        int $entityId,
        string $action,
        ?array $payload = null,
    ): void {
        AuditLog::query()->create([
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'payload' => $payload,
        ]);
    }
}

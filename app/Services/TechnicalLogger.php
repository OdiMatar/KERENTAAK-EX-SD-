<?php

namespace App\Services;

use App\Models\TechnicalLog;
use Illuminate\Support\Facades\Log;
use Throwable;

class TechnicalLogger
{
    /**
     * Store an audit-style technical log without interrupting the user flow.
     *
     * @param  array<string, mixed>  $context
     */
    public function record(string $action, string $message, ?int $userId = null, array $context = []): void
    {
        try {
            TechnicalLog::query()->create([
                'user_id' => $userId,
                'action' => $action,
                'message' => $message,
                'context' => $context,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Technische log kon niet worden opgeslagen.', [
                'action' => $action,
                'message' => $message,
                'exception' => $exception,
            ]);
        }
    }
}

<?php

namespace App\Services;

use App\Models\TechnicalLog;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class TechnicalLogger
{
    private const APPOINTMENT_TECHNICAL_LOG_PATH = 'logs/appoiments technich.log';

    /**
     * Store an audit-style technical log without interrupting the user flow.
     *
     * @param  array<string, mixed>  $context
     */
    public function record(string $action, string $message, ?int $userId = null, array $context = []): void
    {
        $userDetails = $this->userDetails($userId);

        $logData = [
            'datetime' => now()->format('Y-m-d H:i:s'),
            'user_id' => $userId,
            'user_name' => $userDetails['name'],
            'user_email' => $userDetails['email'],
            'action' => $action,
            'message' => $message,
            'context' => $context,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        $this->writeToAppointmentTechnicalLog($logData);

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

    /**
     * @param  array<string, mixed>  $logData
     */
    private function writeToAppointmentTechnicalLog(array $logData): void
    {
        try {
            $path = storage_path(self::APPOINTMENT_TECHNICAL_LOG_PATH);
            $line = json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;

            File::ensureDirectoryExists(dirname($path));
            File::append($path, $line);
        } catch (Throwable $exception) {
            Log::warning('Technische afspraak-log kon niet worden opgeslagen.', [
                'exception' => $exception,
            ]);
        }
    }

    /**
     * @return array{name: string|null, email: string|null}
     */
    private function userDetails(?int $userId): array
    {
        if ($userId === null) {
            return ['name' => null, 'email' => null];
        }

        try {
            $authenticatedUser = auth()->user();

            if ($authenticatedUser instanceof User && $authenticatedUser->id === $userId) {
                return [
                    'name' => $authenticatedUser->name,
                    'email' => $authenticatedUser->email,
                ];
            }

            $user = User::query()
                ->select(['id', 'name', 'email'])
                ->find($userId);

            return [
                'name' => $user?->name,
                'email' => $user?->email,
            ];
        } catch (Throwable) {
            return ['name' => null, 'email' => null];
        }
    }
}

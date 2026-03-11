<?php

namespace App\Traits\Log;

use App\Http\Requests\Log\LogRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use JsonException;

trait Loggable
{
    /**
     * Логирует ошибку, полученную от фронтенда.
     *
     * @param LogRequest $request
     *
     * @return void
     * @throws JsonException
     */
    public function logFrontendError(LogRequest $request): void
    {
        $data = [
            'user_id'       => $request->userId,
            'url'           => $request->requestUrl,
            'status'        => $request->requestStatus,
            'body'          => $request->requestBody,
            'response'      => $request->requestResponse,
            'anyData'      => $request->anyData,
        ];

        $this->writeToLog('frontend', $data);
    }

    /**
     * Логирует произвольные данные, связанные с пользователем.
     *
     * @param array       $data
     * @param string|null $filename Префикс имени файла (без даты). По умолчанию 'user'.
     *
     * @param User|null   $user
     *
     * @return void
     * @throws JsonException
     */
    public function logUserData(array $data, ?string $filename = null, ?User $user = null): void
    {
        $prefix = $filename ?: 'user';
        $logData = array_merge(['user_id' => $user->id??null], $data);

        $this->writeToLog($prefix, $logData);
    }

    /**
     * Записывает данные в лог-файл с префиксом.
     *
     * @param string $prefix
     * @param array  $data
     *
     * @return void
     * @throws JsonException
     */
    protected function writeToLog(string $prefix, array $data): void
    {
        $date = Carbon::now()->format('Y-m-d');
        $filename = $date.'.log';
        $path = storage_path('logs/' . $prefix . '/' . $filename);

        if (!is_dir(dirname($path))) {
            if (!mkdir($concurrentDirectory = dirname($path), 0755, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        $timestamp = Carbon::now()->toDateTimeString();
        $logEntry = sprintf(
            "[%s] %s\n",
            $timestamp,
            json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        file_put_contents($path, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

<?php

namespace App\Logging;

use App\Traits\Log\Loggable;
use JsonException;
use Throwable;

class ExceptionLogger
{
    use Loggable;

    /**
     * @throws JsonException
     */
    public function log(Throwable $e): void
    {
        $user = auth()->user();
        $data = [
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
            'url'       => request()?->fullUrl(),
            'method'    => request()?->method(),
            'ip'        => request()?->ip(),
            'request_body'   => request()?->all(),
        ];

        $this->logUserData($data, 'exception',$user);
    }
}

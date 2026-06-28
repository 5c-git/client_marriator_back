<?php

namespace Modules\YandexSmena\Exceptions;

use RuntimeException;

class YandexSmenaApiException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        private readonly ?array $responseBody = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}

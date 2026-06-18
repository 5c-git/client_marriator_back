<?php

namespace Modules\Questionnaire\Exceptions;

use RuntimeException;

class QuestionnaireProcessingException extends RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

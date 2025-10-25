<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $errors;

    public function __construct(array $errors, string $message = 'Validation failed', int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
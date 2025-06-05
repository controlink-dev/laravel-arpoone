<?php

namespace Controlink\LaravelArpoone\Exceptions;

use Exception;

/**
 * Exception thrown when the Arpoone API returns an error.
 */
class ArpooneRequestException extends Exception
{
    /**
     * Error code returned by the Arpoone API.
     */
    protected ?int $arpooneErrorCode;

    public function __construct(string $message = "", ?int $errorCode = null, int $code = 0, ?Exception $previous = null)
    {
        $this->arpooneErrorCode = $errorCode;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the error code returned by the Arpoone API.
     */
    public function getArpooneErrorCode(): ?int
    {
        return $this->arpooneErrorCode;
    }
}

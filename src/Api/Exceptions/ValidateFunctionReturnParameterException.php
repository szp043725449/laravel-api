<?php

namespace Integration\Api\Exceptions;


use Exception;

class ValidateFunctionReturnParameterException extends \Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message . ' Return parameter error', $code, $previous);
    }

}
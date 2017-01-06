<?php

namespace Integration\Api\Services;


class ErrorMessage extends Message
{
    public function isSuccess()
    {
        return false;
    }
}
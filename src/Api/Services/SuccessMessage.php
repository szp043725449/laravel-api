<?php

namespace Integration\Api\Services;


use Illuminate\Http\Response;

class SuccessMessage extends Message
{
    public function __construct()
    {
        parent::__construct(Response::HTTP_OK, 'ok');
    }

    public function isSuccess()
    {
        return true;
    }
}
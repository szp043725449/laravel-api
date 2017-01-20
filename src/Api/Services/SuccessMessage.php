<?php

namespace Integration\Api\Services;


use Illuminate\Http\Response;

class SuccessMessage extends Message
{
    public function __construct($data = [])
    {
        parent::__construct(Response::HTTP_OK, 'ok', $data);
    }

    public function isSuccess()
    {
        return true;
    }
}
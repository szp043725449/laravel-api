<?php

namespace Integration\Api\Services;

use Illuminate\Http\Response;
use Integration\Api\Configure\Configure;

interface ErrorResponse
{
    /**
     * @param Message $message
     * @param Configure $configure
     * @return Response
     */
    public function send(Message $message, Configure $configure);
}
<?php

namespace Integration\Api\Services;


interface Authentication
{
    /**
     * @return Message
     */
    public function auth();
}
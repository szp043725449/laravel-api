<?php

namespace Integration\Api\Services;

interface  SignMessage
{
    /**
     * @param $appid
     * @return string
     */
    public function getSecret();

    /**
     * @return int
     */
    public function timeSpan();

    /**
     * @return int
     */
    public function happened();

    /**
     * @return array
     */
    public function getSignData();

    /**
     * @return string
     */
    public function getUserSignResult();
}
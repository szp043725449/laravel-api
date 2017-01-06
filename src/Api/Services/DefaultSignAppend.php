<?php

namespace Integration\Api\Services;

use Integration\Api\Exceptions\SignAppendException;

class DefaultSignAppend extends SignAppend
{
    private $key = "";

    public function __construct($type, $key)
    {
        if ($type != self::SIGN_STRING_PREFIX && $type != self::SIGN_STRING_SUFFIX) {
            throw new SignAppendException("signStringAppendType error", 1);
        }
        $this->signStringAppendType = $type;
        $this->key = $key;
    }

    public function getSignStringAppendType()
    {
        return $this->signStringAppendType;
    }

    public function getAppendString()
    {
        return $this->key;
    }
}
<?php

namespace Integration\Api\Services;


interface SignatureOperation
{
    public static function signature(SignMessage $signMessage);
}
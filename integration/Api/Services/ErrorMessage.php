<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/1/5
 * Time: 上午11:08
 */

namespace Integration\Api\Services;


class ErrorMessage extends Message
{
    public function isSuccess()
    {
        return false;
    }
}
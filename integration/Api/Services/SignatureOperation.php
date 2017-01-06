<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/1/5
 * Time: 下午5:05
 */

namespace Integration\Api\Services;


interface SignatureOperation
{
    public static function signature(SignMessage $signMessage);
}
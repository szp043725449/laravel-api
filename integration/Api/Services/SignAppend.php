<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/1/5
 * Time: 下午4:40
 */

namespace Integration\Api\Services;

abstract class SignAppend
{
    const SIGN_STRING_PREFIX = 1;//签名加前缀

    const SIGN_STRING_SUFFIX = 2;//签名加加后缀

    protected $signStringAppendType = self::SIGN_STRING_PREFIX;//签名类型

    /**
     * @return string
     */
    public abstract function getSignStringAppendType();

    /**
     * @return string
     */
    public abstract function getAppendString();
}
<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/1/5
 * Time: 下午4:59
 */

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
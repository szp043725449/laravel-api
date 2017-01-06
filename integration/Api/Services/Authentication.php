<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/1/6
 * Time: 下午1:09
 */

namespace Integration\Api\Services;


interface Authentication
{
    /**
     * @return Message
     */
    public function auth();
}
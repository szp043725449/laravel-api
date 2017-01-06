<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/1/5
 * Time: 上午11:14
 */

namespace Integration\Api\Services;


use Illuminate\Http\Response;

class SuccessMessage extends Message
{
    public function __construct()
    {
        parent::__construct(Response::HTTP_OK, 'ok');
    }

    public function isSuccess()
    {
        return true;
    }
}
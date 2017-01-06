<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/1/5
 * Time: 上午11:08
 */

namespace Integration\Api\Services;


use Illuminate\Http\Response;

class Message
{
    private $code;

    private $message;

    private $data;

    /**
     * Message constructor.
     * @param $code
     * @param $message
     * @param $data
     */
    public function __construct($code, $message, $data = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }


    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getContents()
    {
        return [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'data' => $this->getData()
        ];
    }

    public function isSuccess()
    {
        if ($this->getCode() == Response::HTTP_OK) {
            return true;
        }

        return false;
    }
}
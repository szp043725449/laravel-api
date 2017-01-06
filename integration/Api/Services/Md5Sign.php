<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/1/5
 * Time: 下午4:39
 */

namespace Integration\Api\Services;

use Integration\Api\Exceptions\SignNotDataException;

class Md5Sign extends Sign
{
    CONST DATA_NO = 1;

    CONST DATA_DESC = 2;

    CONST DATA_ASC = 3;

    const SPLICE_DEFAULT = 1;

    private $signResult;//签名结果

    private $signString = "";//签名字符串

    private $signData;//签名数据

    private $spliceTypes;//签名数据拼接样式

    /**
     * @var SignAppend
     */
    private $signAppend;//追加字符串

    private $sort;//签名数据排序类型

    /**
     * Md5Sign constructor.
     * @param array $signData 加密的数据
     * @param SignAppend $signAppend  追加的字符串
     * @param int $sort 排序规则，1=升序排列，2=降序排列
     * @param int $spliceTypes 分割
     */
    public function __construct(array $signData, SignAppend $signAppend, $sort = self::DATA_NO, $spliceTypes = self::SPLICE_DEFAULT)
    {

        $this->spliceTypes = $spliceTypes;

        $this->signData = $signData;

        $this->signAppend = $signAppend;

        $this->sort = $sort;


    }

    public function generatingSignatures()
    {
        /*
        数组排序
            ksort() - 根据键，以升序对关联数组进行排序
            krsort() - 根据键，以降序对关联数组进行排序
        */
        if (count($this->signData)>0) {
            if ($this->sort == self::DATA_DESC) {
                krsort($this->signData);
            } elseif ($this->sort == self::DATA_ASC) {
                ksort($this->signData);
            }
            reset($this->signData);
        } else {
            throw new SignNotDataException("not sign data", 1);
        }

        if ($this->spliceTypes == self::SPLICE_DEFAULT) {
            $this->signString = $this->generatingDefaultSignString($this->signData);
        }

        if ($this->signAppend != null) {
            if ($this->signAppend->getSignStringAppendType() == SignAppend::SIGN_STRING_PREFIX) {
                $this->signString = $this->signAppend->getAppendString().$this->signString;
            } elseif ($this->signAppend->getSignStringAppendType() == SignAppend::SIGN_STRING_SUFFIX) {
                $this->signString .= $this->signAppend->getAppendString();
            }
        }

        $this->signResult = md5($this->signString);

        return $this->signResult;
    }

    public function getSignString()
    {
        return $this->signString;
    }

    public function getSignData()
    {
        return $this->signData;
    }

    public function getSignResult()
    {
        return $this->signResult ? $this->signResult : $this->generatingSignatures();
    }

    public static function verify($sign, Md5Sign $md5Sign)
    {
        if ($sign) {
            if ($sign == $md5Sign->__toString()) {
                return true;
            }
        }

        return false;
    }

    public function __toString()
    {
        return $this->getSignResult();
    }

    private function generatingDefaultSignString(array $signData)
    {
        $signString = "";

        if (count($signData)<=0) {
            return "";
        }

        foreach ($signData as $key => $value) {
            if (urldecode($value) == $value) {
                $signString .= $key."=".urlencode($value)."&";
            } else {
                $signString .= $key."=".$value."&";
            }
        }

        $signString = substr($signString,0,count($signString)-2);
        if(get_magic_quotes_gpc()){$signString = stripslashes($signString);}

        return $signString;
    }
}
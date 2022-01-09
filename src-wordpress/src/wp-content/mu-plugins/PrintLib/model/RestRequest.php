<?php

namespace Xpyun\model;

use Xpyun\util\Xputil;

class RestRequest
{
    /**
     * 开发者ID(芯烨云后台登录账号）
     */
    var $user;
    /**
     * 芯烨云后台开发者密钥
     */
    var $userKey;
    /**
     * 当前UNIX时间戳，10位，精确到秒
     */
    var $timestamp;
    /**
     * 对参数 user + UserKEY + timestamp 拼接后（+号表示连接符）进行SHA1加密得到签名，值为40位小写字符串，其中 UserKEY 为用户开发者密钥
     */
    var $sign;
    /**
     * debug=1返回非json格式的数据。仅测试时候使用
     */
    var $debug;

    function __construct()
    {
//        $this->user = USER_NAME;
//        $this->userKey = USER_KEY;
        $this->debug = "0";
        $this->timestamp = Xputil::getMillisecond();
    }

    public function generateSign()
    {
        $this->sign = Xputil::sign($this->user . $this->userKey . $this->timestamp);
    }
}

?>
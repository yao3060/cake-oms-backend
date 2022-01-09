<?php

namespace Xpyun\util;
class Xputil
{
    /**
     * 哈稀签名
     * @param signSource - 源字符串
     * @return
     */
    public static function sign($signSource)
    {
        $signature = sha1($signSource);

        return $signature;
    }

    /**
     * 当前UNIX时间戳，精确到毫秒
     * @return string
     */
    public static function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}

?>
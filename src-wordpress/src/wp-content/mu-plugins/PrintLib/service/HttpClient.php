<?php
/**
 * 发送http的json请求
 *
 * @param $url 请求url
 * @param $jsonStr 发送的json字符串
 * @return array
 */
namespace Xpyun\service;

use Exception;

class HttpClient
{
    public function http_post_json($url, $jsonStr)
    {
        //print($jsonStr.'<br/>');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);// 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_URL, $url);// 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json;charset=UTF-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);

        return array($httpCode, $response);
    }
}

?>
<?php

namespace Xpyun\service;

use Xpyun\model\XPYunResp;

class PrintService
{
    private const BASE_URL = 'https://open.xpyun.net/api/openapi';

    private function xpyunPostJson($url, $request)
    {
        $jsonRequest = json_encode($request);
        $client = new HttpClient();

        list($returnCode, $returnContent) = $client->http_post_json($url, $jsonRequest);

        $result = new XPYunResp();
        $result->httpStatusCode = $returnCode;
        $result->content = json_decode($returnContent);

        return $result;
    }

    /**
     * 1.批量添加打印机
     * @param restRequest
     * @return
     */
    public function xpYunAddPrinters($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/addPrinters";
        return $this->xpyunPostJson($url, $restRequest);
    }

    /**
     * 2.设置打印机语音类型
     * @param restRequest
     * @return
     */
    public function xpYunSetVoiceType($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/setVoiceType";
        return $this->xpyunPostJson($url, $restRequest);
    }

    /**
     * 3.打印小票订单
     * @param restRequest - 打印订单信息
     * @return
     */
    public function xpYunPrint($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/print";

        return $this->xpyunPostJson($url, $restRequest);
    }

    /**
     * 4.打印标签订单
     * @param restRequest - 打印订单信息
     * @return
     */
    public function xpYunPrintLabel($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/printLabel";

        return $this->xpyunPostJson($url, $restRequest);
    }

    /**
     * 5.批量删除打印机
     * @param restRequest
     * @return
     */
    public function xpYunDelPrinters($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/delPrinters";

        return $this->xpyunPostJson($url, $restRequest);
    }


    /**
     * 6.修改打印机信息
     * @param restRequest
     * @return
     */
    public function xpYunUpdatePrinter($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/updPrinter";
        return $this->xpyunPostJson($url, $restRequest);
    }

    /**
     * 7.清空待打印队列
     * @param restRequest
     * @return
     */
    public function xpYunDelPrinterQueue($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/delPrinterQueue";
        return $this->xpyunPostJson($url, $restRequest);
    }

    /**
     * 8.查询订单是否打印成功
     * @param restRequest
     * @return
     */
    public function xpYunQueryOrderState($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/queryOrderState";

        return $this->xpyunPostJson($url, $restRequest);
    }

    /**
     * 9.查询打印机某天的订单统计数
     * @param restRequest
     * @return
     */
    public function xpYunQueryOrderStatis($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/queryOrderStatis";
        return $this->xpyunPostJson($url, $restRequest);
    }

    /**
     * 10.查询打印机状态
     *
     * 0、离线 1、在线正常 2、在线不正常
     * 备注：异常一般是无纸，离线的判断是打印机与服务器失去联系超过30秒
     * @param restRequest
     * @return
     */
    public function xpYunQueryPrinterStatus($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/queryPrinterStatus";

        return $this->xpyunPostJson($url, $restRequest);
    }

    /**
     * 10.批量查询打印机状态
     *
     * 0、离线 1、在线正常 2、在线不正常
     * 备注：异常一般是无纸，离线的判断是打印机与服务器失去联系超过30秒
     * @param restRequest
     * @return
     */
    public function xpYunQueryPrintersStatus($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/queryPrintersStatus";

        return $this->xpyunPostJson($url, $restRequest);
    }

    /**
     * 11.云喇叭播放语音
     * @param restRequest - 播放语音信息
     * @return
     */
    public function xpYunPlayVoice($restRequest)
    {
        $url = self::BASE_URL . "/xprinter/playVoice";

        return $this->xpyunPostJson($url, $restRequest);
    }
	
	/**
	 * 12.POS指令
	 * @param restRequest
	 * @return
	 */
	public function xpYunPos($restRequest)
	{
	    $url = self::BASE_URL . "/xprinter/pos";
	
	    return $this->xpyunPostJson($url, $restRequest);
	}
	
	/**
	 * 13.钱箱控制
	 * @param restRequest
	 * @return
	 */
	public function xpYunControlBox($restRequest)
	{
	    $url = self::BASE_URL . "/xprinter/controlBox";
	
	    return $this->xpyunPostJson($url, $restRequest);
	}
}

?>
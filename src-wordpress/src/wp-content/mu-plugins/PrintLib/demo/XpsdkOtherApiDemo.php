<?php

use Xpyun\model\AddPrinterRequest;
use Xpyun\model\AddPrinterRequestItem;
use Xpyun\model\DelPrinterRequest;
use Xpyun\model\PrinterRequest;
use Xpyun\model\QueryOrderStateRequest;
use Xpyun\model\QueryOrderStatisRequest;
use Xpyun\model\SetVoiceTypeRequest;
use Xpyun\model\UpdPrinterRequest;
use Xpyun\model\VoiceRequest;
use Xpyun\service\PrintService;

/**
 * Class XpsdkOtherApiDemo
 * 打印机管理
 */
class XpsdkOtherApiDemo
{
    /**
     * 打印服务对象实例化
     */
    private $service;

    public function __construct()
    {
        $this->service = new PrintService();
    }

    /**
     * 批量地添加打印机
     */
    public function addPrintersTest()
    {
        //打印机列表
        $request = new AddPrinterRequest();

        $requestItem1 = new AddPrinterRequestItem();
        // 打印机编号，必须是真实的打印机编号，否在会导致后续api无法打印
        $requestItem1->sn = OK_PRINTER_SN;
        //打印机名称
        $requestItem1->name = "测试打印机";

        $requestItems = array($requestItem1);

        $request->generateSign();
        //*必填*：items:数组元素为 json 对象：
        //{"name":"打印机名称","sn":"打印机编号"}
        //其中打印机编号 sn 和名称 name 字段为必填项，每次最多添加50台
        $request->items = $requestItems;

        $result = $this->service->xpYunAddPrinters($request);
        //$result->content->data:返回1个 json 对象，包含成功和失败的信息，详看https://www.xpyun.net/open/index.html示例
        print $result->content->code . "\n";
        print $result->content->msg . "\n";
        var_dump($result->content->data);
    }

    /**
     * 设置打印机语音类型
     * 声音类型： 0真人语音（大） 1真人语音（中） 2真人语音（小） 3 嘀嘀声  4 静音
     */
    function setVoiceTypeTest()
    {
        $request = new SetVoiceTypeRequest();
        $request->generateSign();
        //*必填*：打印机编号
        $request->sn = OK_PRINTER_SN;

        //*必填*：声音类型： 0真人语音（大） 1真人语音（中） 2真人语音（小） 3 嘀嘀声  4 静音
        $request->voiceType = 1;

        $result = $this->service->xpYunSetVoiceType($request);
        //$result->content->data:返回布尔类型：true 表示设置成功 false 表示设置失败
        print $result->content->code;
        print $result->content->msg;
        var_dump($result->content->data);
    }

    /**
     * 批量删除打印机
     */
    function delPrintersTest()
    {
        $request = new DelPrinterRequest();
        $request->generateSign();
        //*必填*：打印机编号集合，类型为字符串数组
        $snlist = array();
        //*必填*：打印机编号
        $snlist[0] = OK_PRINTER_SN;
        $request->snlist = $snlist;

        $result = $this->service->xpYunDelPrinters($request);
        //$result->content->data:返回1个 json 对象，包含成功和失败的信息，详看https://www.xpyun.net/open/index.html示例
        print $result->content->code . "\n";
        print $result->content->msg . "\n";
        var_dump($result->content->data);
    }

    /**
     * 修改打印机信息
     */
    function updPrinterTest()
    {
        $request = new UpdPrinterRequest();
        $request->generateSign();
        //*必填*：打印机编号
        $request->sn = OK_PRINTER_SN;
        //*必填*：打印机名称
        $request->name = "X58C75432";

        $result = $this->service->xpYunUpdatePrinter($request);
        //$result->content->data:返回布尔类型：true 表示成功 false 表示失败
        print $result->content->code . "\n";
        print $result->content->msg . "\n";
        print $result->content->data . "\n";
        var_dump($result->content->data);
    }

    /**
     * 清空待打印队列
     */
    function delPrinterQueueTest()
    {
        $request = new PrinterRequest();
        $request->generateSign();
        //*必填*：打印机编号
        $request->sn = OK_PRINTER_SN;

        $result = $this->service->xpYunDelPrinterQueue($request);
        //$result->content->data:返回布尔类型：true 表示成功 false 表示失败
        print $result->content->code . "\n";
        print $result->content->msg . "\n";
        print $result->content->data . "\n";
        var_dump($result->content->data);
    }

    /**
     * 查询订单是否打印成功
     */
    function queryOrderStateTest()
    {
        $request = new QueryOrderStateRequest();
        $request->generateSign();
        // *必填*：订单编号，由“打印订单”接口返回
        $request->orderId = "OM30102113431016894227";
        $result = $this->service->xpYunQueryOrderState($request);
        //$result->content->data:返回布尔类型,已打印返回true,未打印返回false
        print $result->content->code . "\n";
        print $result->content->msg . "\n";
        print $result->content->data . "\n";
        var_dump($result->content->data);
    }

    /**
     * 查询打印机某天的订单统计数
     */
    function queryOrderStatisTest()
    {
        $request = new QueryOrderStatisRequest();
        $request->generateSign();
        //*必填*：打印机编号
        $request->sn = OK_PRINTER_SN;
        //*必填*：查询日期，格式yyyy-MM-dd，如：2020-10-21
        $request->date = "2020-10-21";
        $result = $this->service->xpYunQueryOrderStatis($request);
        //$result->content->data:json对象，返回已打印订单数和等待打印订单数，如：{"printed": 2, "waiting": 0}
        print $result->content->code;
        print $result->content->msg;
        var_dump($result->content->data);
    }

    /**
     * 查询打印机状态
     */
    function queryPrinterStatusTest()
    {
        $request = new PrinterRequest();
        $request->generateSign();
        //*必填*：打印机编号
        $request->sn = OK_PRINTER_SN;

        $result = $this->service->xpYunQueryPrinterStatus($request);
        //$result->content->data:返回打印机状态值，共三种：
        //0 表示离线
        //1 表示在线正常
        //2 表示在线缺纸
        //备注：离线的判断是打印机与服务器失去联系超过 30 秒

        print $result->content->code . "\n";
        print $result->content->msg . "\n";
        print $result->content->data . "\n";
        var_dump($result->content->data);
    }

    /**
     * 金额播报
     */
    function playVoiceTest()
    {
        $request = new VoiceRequest();
        $request->generateSign();
        //*必填*：打印机编号
        $request->sn = OK_PRINTER_SN;
        //支付方式：
        //取值范围41~55：
        //支付宝 41、微信 42、云支付 43、银联刷卡 44、银联支付 45、会员卡消费 46、会员卡充值 47、翼支付 48、成功收款 49、嘉联支付 50、壹钱包 51、京东支付 52、快钱支付 53、威支付 54、享钱支付 55
        //仅用于支持金额播报的芯烨云打印机。
        $request->payType = 41;
        //支付与否：
        //取值范围59~61：
        //退款 59 到账 60 消费 61。
        //仅用于支持金额播报的芯烨云打印机。
        $request->payMode = 59;

        $request->money = 24.15;
        //支付金额：
        //最多允许保留2位小数。
        //仅用于支持金额播报的芯烨云打印机。
        $result = $this->service->xpYunPlayVoice($request);
        //$result->content->data:正确返回0
        print $result->content->code . "\n";
        print $result->content->msg . "\n";
        print $result->content->data . "\n";
        var_dump($result->content->data);
    }
}

?>
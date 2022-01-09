<?php

namespace Xpyun\model;
class PrintRequest extends RestRequest
{

    /**
     * 打印机编号
     */
    var $sn;

    /**
     * 打印内容,不能超过5000字节
     */
    var $content;

    /**
     * 打印份数，默认为1
     */
    var $copies = 1;

    /**
     * 打印模式，默认为0
     */
    var $mode = 0;

    /**
     * 支付方式41~55：支付宝 微信 ...
     */
    var $payType;
    /**
     * 支付与否59~61：退款 到账 消费
     */
    var $payMode;
    /**
     * 支付金额
     */
    var $money;
	/**
     * 声音播放模式，0 为取消订单模式，1 为静音模式，2 为来单播放模式，默认为 2 来单播放模式
     */
    var $voice = 2;
}

?>
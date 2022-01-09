<?php

namespace Xpyun\model;
class VoiceRequest extends RestRequest
{

    /**
     * 打印机编号
     */
    var $sn;

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

}

?>
<?php

namespace Xpyun\model;
class SetVoiceTypeRequest extends RestRequest
{

    /**
     * 打印机编号
     */
    var $sn;

    /**
     * 声音类型： 0真人语音（大） 1真人语音（中） 2真人语音（小） 3 嘀嘀声  4 静音
     */
    var $voiceType;
}

?>
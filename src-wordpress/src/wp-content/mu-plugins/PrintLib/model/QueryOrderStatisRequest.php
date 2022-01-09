<?php

namespace Xpyun\model;
class QueryOrderStatisRequest extends RestRequest
{

    /**
     * 打印机编号
     */
    var $sn;
    /**
     * 查询日期，格式YY-MM-DD，如：2016-09-20
     */
    var $date;

}

?>
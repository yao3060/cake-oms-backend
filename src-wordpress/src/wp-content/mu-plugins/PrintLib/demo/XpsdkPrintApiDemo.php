<?php

use Xpyun\model\PrintRequest;
use Xpyun\service\PrintService;
use Xpyun\util\NoteFormatter;

/**
 * Class XpsdkPrintApiDemo
 * 打印示例
 */
class XpsdkPrintApiDemo
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
     * 小票打印字体对齐样例，不支持金额播报
     * 注意：对齐标签L C R CB 请勿嵌套使用，嵌套使用内层标签有效，外层失效；
     * 同一行请勿使用多个对齐标签，否则只有最后一个对齐标签有效
     */
    public function printFontAlign()
    {
        /**
         * <BR>：换行符（同一行有闭合标签(如 </C>)则应放到闭合标签前面, 连续两个换行符<BR><BR>可以表示加一空行）
         *  <L></L>：左对齐
         *  <C></C>：居中对齐
         *  <R></R>：右对齐
         *  注意：同一行内容不能使用多种对齐方式，可通过补空格方式自定义对齐样式。
         *       58mm的机器，一行打印16个汉字，32个字母
         *       80mm的机器，一行打印24个汉字，48个字母
         *
         *  <N></N>：字体正常大小
         *  <HB></HB>：字体变高一倍
         *  <WB></WB>：字体变宽一倍
         *  <B></B>：字体放大一倍
         *  <CB></CB>：字体放大一倍居中
         *  <HB2></HB2>：字体变高二倍
         *  <WB2></WB2>：字体变宽二倍
         *  <B2></B2>：字体放大二倍
         *  <BOLD></BOLD>：字体加粗
         *  <IMG></IMG>：打印LOGO图片，需登录开放平台在【打印机管理=》设备管理】下通过设置LOGO功能进行上传。此处直接写入
         *             空标签, 如 <IMG></IMG> 即可, 具体可参考样例。
         *             图片宽度设置：可以通过 <IMG> 标签名称自定义，如 <IMG60> 表示宽度为60，相应的闭合标签 </IMG>
         *             不需要指定高度。<IMG> 标签不指定宽度默认为40，最小值为20，最大值为100
         *  <QR></QR>：二维码（标签内容是二维码值, 最大不能超过256个字符, 单个订单最多只能打印一个二维码）。
         *             二维码宽度设置：可以通过 <QR> 标签名称自定义，如 <QR180> 表示宽度为180，相应的闭合标签 </QR>
         *             不需要指定宽度。<QR> 标签不指定宽度默认为110，最小值为90，最大值为180
         *  <BARCODE></BARCODE>：条形码（标签内容是条形码值）
         */
        $printContent = <<<EOF
不加标签：默认字体大小<BR>
<BR>
L标签：<L>左对齐<BR></L>
<BR>
R标签：<R>右对齐<BR></R>
<BR>
C标签：<C>居中对齐<BR></C>
<BR>
N标签：<N>字体正常大小<BR></N>
<BR>
HB标签：<HB>字体变高一倍<BR></HB>
<BR>
WB标签：<WB>字体变宽一倍<BR></WB>
<BR>
B标签：<B>字体放大一倍<BR></B>
<BR>
HB2标签：<HB2>字体变高二倍<BR></HB2>
<BR>
WB2标签：<WB2>字体变宽二倍<BR></WB2>
<BR>
B2标签：<B2>字体放大二倍<BR></B2>
<BR>
BOLD标签：<BOLD>字体加粗<BR></BOLD>
EOF;

        $printContent = $printContent . '<BR>';
        // 嵌套使用对齐和字体
        $printContent = $printContent . '<C>嵌套使用：<BOLD>居中加粗</BOLD><BR></C>';

        // 打印条形码和二维码
        $printContent = $printContent . '<BR>';
        $printContent = $printContent . '<C><BARCODE>9884822189</BARCODE></C>';
        $printContent = $printContent . '<C><QR>https://www.xpyun.net</QR></C>';


        $request = new PrintRequest();
        $request->generateSign();

        //*必填*：打印机编号
        $request->sn = OK_PRINTER_SN;

        //*必填*：打印内容,不能超过12K
        $request->content = $printContent;

        //打印份数，默认为1
        $request->copies = 1;
		
		//声音播放模式，0 为取消订单模式，1 为静音模式，2 为来单播放模式，3为有用户申请退单了。默认为 2 来单播放模式
		$request->voice = 2;

        //打印模式：
        //值为 0 或不指定则会检查打印机是否在线，如果不在线 则不生成打印订单，直接返回设备不在线状态码；如果在线则生成打印订单，并返回打印订单号。
        //值为 1不检查打印机是否在线，直接生成打印订单，并返回打印订单号。如果打印机不在线，订单将缓存在打印队列中，打印机正常在线时会自动打印。
        $request->mode = 0;

        $result = $this->service->xpYunPrint($request);
        //$result->content->data:正确返回订单编号
        print $result->content->code . "\n";
        print $result->content->msg . "\n";

        //data:正确返回订单编号
        print $result->content->data . "\n";
    }

    /**
     * 小票打印字体对齐样例，支持金额播报
     * 注意：对齐标签L C R CB 请勿嵌套使用，嵌套使用内层标签有效，外层失效；
     * 同一行请勿使用多个对齐标签，否则只有最后一个对齐标签有效
     */
    public function printFontAlignVoiceSupport()
    {
        /**
         * <BR>：换行符（同一行有闭合标签(如 </C>)则应放到闭合标签前面, 连续两个换行符<BR><BR>可以表示加一空行）
         *  <L></L>：左对齐
         *  <C></C>：居中对齐
         *  <R></R>：右对齐
         *  注意：同一行内容不能使用多种对齐方式，可通过补空格方式自定义对齐样式。
         *       58mm的机器，一行打印16个汉字，32个字母
         *       80mm的机器，一行打印24个汉字，48个字母
         *
         *  <N></N>：字体正常大小
         *  <HB></HB>：字体变高一倍
         *  <WB></WB>：字体变宽一倍
         *  <B></B>：字体放大一倍
         *  <CB></CB>：字体放大一倍居中
         *  <HB2></HB2>：字体变高二倍
         *  <WB2></WB2>：字体变宽二倍
         *  <B2></B2>：字体放大二倍
         *  <BOLD></BOLD>：字体加粗
         *  <IMG></IMG>：打印LOGO图片，需登录开放平台在【打印机管理=》设备管理】下通过设置LOGO功能进行上传。此处直接写入
         *             空标签, 如 <IMG></IMG> 即可, 具体可参考样例。
         *             图片宽度设置：可以通过 <IMG> 标签名称自定义，如 <IMG60> 表示宽度为60，相应的闭合标签 </IMG>
         *             不需要指定高度。<IMG> 标签不指定宽度默认为40，最小值为20，最大值为100
         *  <QR></QR>：二维码（标签内容是二维码值, 最大不能超过256个字符, 单个订单最多只能打印一个二维码）。
         *             二维码宽度设置：可以通过 <QR> 标签名称自定义，如 <QR180> 表示宽度为180，相应的闭合标签 </QR>
         *             不需要指定宽度。<QR> 标签不指定宽度默认为110，最小值为90，最大值为180
         *  <BARCODE></BARCODE>：条形码（标签内容是条形码值）
         */
        $printContent = <<<EOF
不加标签：默认字体大小<BR>
<BR>
L标签：<L>左对齐<BR></L>
<BR>
R标签：<R>右对齐<BR></R>
<BR>
C标签：<C>居中对齐<BR></C>
<BR>
N标签：<N>字体正常大小<BR></N>
<BR>
HB标签：<HB>字体变高一倍<BR></HB>
<BR>
WB标签：<WB>字体变宽一倍<BR></WB>
<BR>
B标签：<B>字体放大一倍<BR></B>
<BR>
HB2标签：<HB2>字体变高二倍<BR></HB2>
<BR>
WB2标签：<WB2>字体变宽二倍<BR></WB2>
<BR>
B2标签：<B2>字体放大二倍<BR></B2>
<BR>
BOLD标签：<BOLD>字体加粗<BR></BOLD>
<BR>
<C>嵌套使用：<BOLD>居中加粗</BOLD><BR></C>
<BR>
<C><BARCODE>9884822189</BARCODE></C>
<C><QR>https://www.xpyun.net</QR></C>
EOF;

        $request = new PrintRequest();
        $request->generateSign();

        //*必填*：打印机编号
        $request->sn = OK_PRINTER_SN;

        //*必填*：打印内容,不能超过12K
        $request->content = $printContent;

        //打印份数，默认为1
        $request->copies = 1;
		
		//声音播放模式，0 为取消订单模式，1 为静音模式，2 为来单播放模式，3为有用户申请退单了。默认为 2 来单播放模式
		$request->voice = 2;

        //打印模式：
        //值为 0 或不指定则会检查打印机是否在线，如果不在线 则不生成打印订单，直接返回设备不在线状态码；如果在线则生成打印订单，并返回打印订单号。
        //值为 1不检查打印机是否在线，直接生成打印订单，并返回打印订单号。如果打印机不在线，订单将缓存在打印队列中，打印机正常在线时会自动打印。
        $request->mode = 0;

        //支付方式：
        //取值范围41~55：
        //支付宝 41、微信 42、云支付 43、银联刷卡 44、银联支付 45、会员卡消费 46、会员卡充值 47、翼支付 48、成功收款 49、嘉联支付 50、壹钱包 51、京东支付 52、快钱支付 53、威支付 54、享钱支付 55
        //仅用于支持金额播报的芯烨云打印机。
        $request->payType = 41;

        //支付与否：
        //取值范围59~61：
        //退款 59 到账 60 消费 61。
        //仅用于支持金额播报的芯烨云打印机。
        $request->payMode = 60;

        //支付金额：
        //最多允许保留2位小数。
        //仅用于支持金额播报的芯烨云打印机。
        $request->money = 20.15;

        $result = $this->service->xpYunPrint($request);
        //$result->content->data:正确返回订单编号
        print $result->content->code . "\n";
        print $result->content->msg . "\n";
        print $result->content->data . "\n";
    }

    /**
     * 小票打印综合排版样例，不支持金额播报
     * 58mm打印机一行可打印机32个字符
     */
    function printComplexReceipt()
    {
        /**
         * <BR>：换行符（同一行有闭合标签(如 </C>)则应放到闭合标签前面, 连续两个换行符<BR><BR>可以表示加一空行）
         *  <L></L>：左对齐
         *  <C></C>：居中对齐
         *  <R></R>：右对齐
         *  注意：同一行内容不能使用多种对齐方式，可通过补空格方式自定义对齐样式。
         *       58mm的机器，一行打印16个汉字，32个字母
         *       80mm的机器，一行打印24个汉字，48个字母
         *
         *  <N></N>：字体正常大小
         *  <HB></HB>：字体变高一倍
         *  <WB></WB>：字体变宽一倍
         *  <B></B>：字体放大一倍
         *  <CB></CB>：字体放大一倍居中
         *  <HB2></HB2>：字体变高二倍
         *  <WB2></WB2>：字体变宽二倍
         *  <B2></B2>：字体放大二倍
         *  <BOLD></BOLD>：字体加粗
         *  <IMG></IMG>：打印LOGO图片，需登录开放平台在【打印机管理=》设备管理】下通过设置LOGO功能进行上传。此处直接写入
         *             空标签, 如 <IMG></IMG> 即可, 具体可参考样例。
         *             图片宽度设置：可以通过 <IMG> 标签名称自定义，如 <IMG60> 表示宽度为60，相应的闭合标签 </IMG>
         *             不需要指定高度。<IMG> 标签不指定宽度默认为40，最小值为20，最大值为100
         *  <QR></QR>：二维码（标签内容是二维码值, 最大不能超过256个字符, 单个订单最多只能打印一个二维码）。
         *             二维码宽度设置：可以通过 <QR> 标签名称自定义，如 <QR180> 表示宽度为180，相应的闭合标签 </QR>
         *             不需要指定宽度。<QR> 标签不指定宽度默认为110，最小值为90，最大值为180
         *  <BARCODE></BARCODE>：条形码（标签内容是条形码值）
         */
        $printContent = "";

        $printContent = $printContent . "<C>" . "<B>芯烨云小票</B>" . "<BR></C>";
        $printContent = $printContent . "<BR>";

        $printContent = $printContent . "菜名" . str_repeat(" ", 16) . "数量" . str_repeat(" ", 2) . "单价" . str_repeat(" ", 2)
            . "<BR>";
        $printContent = $printContent . str_repeat("-", 32) . "<BR>";
        $printContent = $printContent . NoteFormatter::formatPrintOrderItem("可乐鸡翅", 2, 9.99);
        $printContent = $printContent . NoteFormatter::formatPrintOrderItem("水煮鱼特辣", 1, 108.0);
        $printContent = $printContent . NoteFormatter::formatPrintOrderItem("豪华版超级无敌龙虾炒饭", 1, 99.9);
        $printContent = $printContent . NoteFormatter::formatPrintOrderItem("炭烤鳕鱼", 5, 19.99);
        $printContent = $printContent . str_repeat("-", 32) . "<BR>";
        $printContent = $printContent . "<R>" . "合计：" . "327.83" . "元" . "<BR></R>";

        $printContent = $printContent . "<BR>";
        $printContent = $printContent . "<L>"
        . "客户地址：" . "珠海市香洲区xx路xx号" . "<BR>"
        . "客户电话：" . "1363*****88" . "<BR>"
        . "下单时间：" . "2020-9-9 15:07:57" . "<BR>"
        . "备注：" . "少放辣 不吃香菜" . "<BR>";

        $printContent = $printContent . "<C>"
            . "<QRCODE s=6 e=L l=center>https://www.xpyun.net</QRCODE>"
            . "</C>";

        print $printContent;

        $request = new PrintRequest();
        $request->generateSign();

        //*必填*：打印机编号
        $request->sn = OK_PRINTER_SN;

        //*必填*：打印内容,不能超过12K
        $request->content = $printContent;

        //打印份数，默认为1
        $request->copies = 1;
		
		//声音播放模式，0 为取消订单模式，1 为静音模式，2 为来单播放模式，3为有用户申请退单了。默认为 2 来单播放模式
		$request->voice = 2;
        //打印模式：
        //值为 0 或不指定则会检查打印机是否在线，如果不在线 则不生成打印订单，直接返回设备不在线状态码；如果在线则生成打印订单，并返回打印订单号。
        //值为 1不检查打印机是否在线，直接生成打印订单，并返回打印订单号。如果打印机不在线，订单将缓存在打印队列中，打印机正常在线时会自动打印。
        $request->mode = 0;

        $result = $this->service->xpYunPrint($request);
        print $result->content->code . "\n";
        print $result->content->msg . "\n";

        //data:正确返回订单编号
        print $result->content->data . "\n";
    }


    /**
     * 小票打印综合排版样例，支持金额播报
     * 58mm打印机一行可打印机32个字符
     */
    function printComplexReceiptVoiceSupport()
    {
        /**
         * <BR>：换行符（同一行有闭合标签(如 </C>)则应放到闭合标签前面, 连续两个换行符<BR><BR>可以表示加一空行）
         *  <L></L>：左对齐
         *  <C></C>：居中对齐
         *  <R></R>：右对齐
         *  注意：同一行内容不能使用多种对齐方式，可通过补空格方式自定义对齐样式。
         *       58mm的机器，一行打印16个汉字，32个字母
         *       80mm的机器，一行打印24个汉字，48个字母
         *
         *  <N></N>：字体正常大小
         *  <HB></HB>：字体变高一倍
         *  <WB></WB>：字体变宽一倍
         *  <B></B>：字体放大一倍
         *  <CB></CB>：字体放大一倍居中
         *  <HB2></HB2>：字体变高二倍
         *  <WB2></WB2>：字体变宽二倍
         *  <B2></B2>：字体放大二倍
         *  <BOLD></BOLD>：字体加粗
         *  <IMG></IMG>：打印LOGO图片，需登录开放平台在【打印机管理=》设备管理】下通过设置LOGO功能进行上传。此处直接写入
         *             空标签, 如 <IMG></IMG> 即可, 具体可参考样例。
         *             图片宽度设置：可以通过 <IMG> 标签名称自定义，如 <IMG60> 表示宽度为60，相应的闭合标签 </IMG>
         *             不需要指定高度。<IMG> 标签不指定宽度默认为40，最小值为20，最大值为100
         *  <QR></QR>：二维码（标签内容是二维码值, 最大不能超过256个字符, 单个订单最多只能打印一个二维码）。
         *             二维码宽度设置：可以通过 <QR> 标签名称自定义，如 <QR180> 表示宽度为180，相应的闭合标签 </QR>
         *             不需要指定宽度。<QR> 标签不指定宽度默认为110，最小值为90，最大值为180
         *  <BARCODE></BARCODE>：条形码（标签内容是条形码值）
         */
        $printContent = "";

        $printContent = $printContent . "<C>" . "<B>芯烨云小票</B>" . "<BR></C>";
        $printContent = $printContent . "<BR>";

        $printContent = $printContent . "菜名" . str_repeat(" ", 16) . "数量" . str_repeat(" ", 2) . "单价" . str_repeat(" ", 2)
            . "<BR>";
        $printContent = $printContent . str_repeat("-", 32) . "<BR>";
        $printContent = $printContent . NoteFormatter::formatPrintOrderItem("可乐鸡翅", 2, 9.99);
        $printContent = $printContent . NoteFormatter::formatPrintOrderItem("水煮鱼特辣", 1, 108.0);
        $printContent = $printContent . NoteFormatter::formatPrintOrderItem("豪华版超级无敌龙虾炒饭", 1, 99.9);
        $printContent = $printContent . NoteFormatter::formatPrintOrderItem("炭烤鳕鱼", 5, 19.99);
        $printContent = $printContent . str_repeat("-", 32) . "<BR>";
        $printContent = $printContent . "<R>" . "合计：" . "327.83" . "元" . "<BR></R>";

        $printContent = $printContent . "<BR>";
        $printContent = $printContent . "<L>"
        . "客户地址：" . "珠海市香洲区xx路xx号" . "<BR>"
        . "客户电话：" . "1363*****88" . "<BR>"
        . "下单时间：" . "2020-9-9 15:07:57" . "<BR>"
        . "备注：" . "少放辣 不吃香菜" . "<BR>";

        $printContent = $printContent . "<C>"
            . "<QRCODE s=6 e=L l=center>https://www.xpyun.net</QRCODE>"
            . "</C>";

        $request = new PrintRequest();
        $request->generateSign();

        //*必填*：打印机编号
        $request->sn = OK_PRINTER_SN;

        //*必填*：打印内容,不能超过12K
        $request->content = $printContent;

        //打印份数，默认为1
        $request->copies = 1;
		
		//声音播放模式，0 为取消订单模式，1 为静音模式，2 为来单播放模式，3为有用户申请退单了。默认为 2 来单播放模式
		$request->voice = 2;

        //打印模式：
        //值为 0 或不指定则会检查打印机是否在线，如果不在线 则不生成打印订单，直接返回设备不在线状态码；如果在线则生成打印订单，并返回打印订单号。
        //值为 1不检查打印机是否在线，直接生成打印订单，并返回打印订单号。如果打印机不在线，订单将缓存在打印队列中，打印机正常在线时会自动打印。
        $request->mode = 0;

        //支付方式：
        //取值范围41~55：
        //支付宝 41、微信 42、云支付 43、银联刷卡 44、银联支付 45、会员卡消费 46、会员卡充值 47、翼支付 48、成功收款 49、嘉联支付 50、壹钱包 51、京东支付 52、快钱支付 53、威支付 54、享钱支付 55
        //仅用于支持金额播报的芯烨云打印机。
        $request->payType = 41;

        //支付与否：
        //取值范围59~61：
        //退款 59 到账 60 消费 61。
        //仅用于支持金额播报的芯烨云打印机。
        $request->payMode = 60;

        //支付金额：
        //最多允许保留2位小数。
        //仅用于支持金额播报的芯烨云打印机。
        $request->money = 20.15;

        $result = $this->service->xpYunPrint($request);
        print $result->content->code . "\n";
        print $result->content->msg . "\n";

        //data:正确返回订单编号
        print $result->content->data . "\n";
    }

    /**
     * 标签打印综合排版样例
     * 如何确定坐标：坐标原点位于左上角，x轴是从左往右，y轴是从上往下；
     * 根据测试，x轴最大值=标签纸宽度*8，y轴最大值=标签纸高度*8，
     * 如标签纸尺寸为40*30，x轴最大值=40*8=320，y轴最大值=30*8=240
     * 实际排版效果需要用户按实际纸张尺寸和需求自行排版
     *
     * 打印内容内（标签除外）大于号和小于号需要经过转译才能正常打印。其中，“<”用“&lt”表示，“>”用“&gt”表示；1mm=8dots。
     */
    function printLabel()
    {
        /**
         * <PAGE></PAGE>：
         *  分页，用于支持打印多张不同的标签页面（最多10张），不使用该标签表示所有元素只打印在一个标签页
         *
         *  <SIZE>width,height</SIZE>：
         *  设置标签纸宽高，width 标签纸宽度(不含背纸)，height 标签纸高度(不含背纸)，单位mm，如<SIZE>40,30</SIZE>
         *
         *  <TEXT x="10" y="100" w="1" h="2" r="0">文本内容</TEXT>：
         *  打印文本，其中：
         *  属性 x 为水平方向起始点坐标（默认为0）
         *  属性 y 为垂直方向起始点坐标（默认为0）
         *  属性 w 为文字宽度放大倍率1-10（默认为1）
         *  属性 h 为文字高度放大倍率1-10（默认为1）
         *  属性 r 为文字旋转角度(顺时针方向，默认为0)：
         *  0     0度
         *  90   90度
         *  180 180度
         *  270 270度
         *
         *  <BC128 x="10" y="100" h="60" s="1" n="1" w="1" r="0">1234567</BC128>：
         *  打印code128一维码，其中：
         *  属性 x 为水平方向起始点坐标（默认为0）
         *  属性 y 为垂直方向起始点坐标（默认为0）
         *  属性 h 为条形码高度（默认为48）
         *  属性 s 是否人眼可识：0 不可识，1 可识（默认为1）
         *  属性 n 为窄 bar 宽度，以点(dot)表示（默认为1）
         *  属性 w 为宽 bar 宽度，以点(dot)表示（默认为1）
         *  属性 r 为文字旋转角度 (顺时针方向，默认为0)：
         *  0     0度
         *  90   90度
         *  180 180度
         *  270 270度
         *
         *  <BC39 x="10" y="100" h="60" s="1" n="1" w="1" r="0">1234567</BC39>：
         *  打印code39一维码，其中：
         *  属性 x 为水平方向起始点坐标（默认为0）
         *  属性 y 为垂直方向起始点坐标（默认为0）
         *          *  属性 h 为条形码高度（默认为48）
         *          *  属性 s 是否人眼可识：0 不可识，1 可识（默认为1）
         *          *  属性 n 为窄 bar 宽度，以点(dot)表示（默认为1）
         *          *  属性 w 为宽 bar 宽度，以点(dot)表示（默认为2）
         *          *  属性 r 为文字旋转角度(顺时针方向，默认为0)：
         *          *  0     0度
         *          *  90   90度
         *          *  180 180度
         *          *  270 270度
         *          *
         *          *  <QR x="20" y="20" w="160" e="H">二维码内容</QR>：
         *          *  打印二维码，其中：
         *          *  属性 x 为水平方向起始点坐标（默认为0）
         *          *  属性 y 为垂直方向起始点坐标（默认为0）
         *          *  属性 w 为二维码宽度（默认为160）
         *          *  属性 e 为纠错等级：L 7% M 15% Q 25% H 30%（默认为H）
         *          *  标签内容是二维码值, 最大不能超过256个字符
         *          *  注意：单个订单最多只能打印一个二维码
         * <IMG x="16" y="32" w="100">：
         * 打印LOGO图片，需登录开放平台在【打印机管理=》设备管理】下通过设置LOGO功能进行上传。此处直接
         * 写入空标签,若指定了<PAGE>标签，<IMG>标签应该放到<PAGE>标签里面， <IMG>, 如 <IMG>即可, 具
         * 体可参考样例。其中：
         *    * 属性 x 为水平方向起始点坐标（默认为0）
         *    * 属性 y 为垂直方向起始点坐标（默认为0）
         *    * 属性 w 为logo图片最大宽度（默认为50），最小值为20，最大值为100。logo图片的高度和宽度相等
         */


        //第一个标签
        $printContent = "<PAGE>"
            . "<SIZE>40,30</SIZE>" // 设定标签纸尺寸
            . "<TEXT x=\"8\" y=\"8\" w=\"1\" h=\"1\" r=\"0\">"
            . "#001" . str_repeat(" ", 4)
            . "一号桌" . str_repeat(" ", 4)
            . "1/3"
            . "</TEXT>"
            . "<TEXT x=\"8\" y=\"96\" w=\"2\" h=\"2\" r=\"0\">"
            . "黄金炒饭"
            . "</TEXT>"
            . "<TEXT x=\"8\" y=\"200\" w=\"1\" h=\"1\" r=\"0\">"
            . "王女士" . str_repeat(" ", 4)
            . "136****3388"
            . "</TEXT>"
            . "</PAGE>";

        //第二个标签
        $printContent = $printContent . "<PAGE>"
            . "<TEXT x=\"8\" y=\"8\" w=\"1\" h=\"1\" r=\"0\">"
            . "#001" . str_repeat(" ", 4)
            . "一号桌" . str_repeat(" ", 4)
            . "2/3"
            . "</TEXT>"
            . "<TEXT x=\"8\" y=\"96\" w=\"2\" h=\"2\" r=\"0\">"
            . "凉拌青瓜"
            . "</TEXT>"
            . "<TEXT x=\"8\" y=\"200\" w=\"1\" h=\"1\" r=\"0\">"
            . "王女士" . str_repeat(" ", 4)
            . "136****3388"
            . "</TEXT>"
            . "</PAGE>";

        //第三个标签
        $printContent = $printContent . "<PAGE>"
            . "<TEXT x=\"8\" y=\"8\" w=\"1\" h=\"1\" r=\"0\">"
            . "#001" . str_repeat(" ", 4)
            . "一号桌" . str_repeat(" ", 4)
            . "3/3"
            . "</TEXT>"
            . "<TEXT x=\"8\" y=\"96\" w=\"2\" h=\"2\" r=\"0\">"
            . "老刘家肉夹馍"
            . "</TEXT>"
            . "<TEXT x=\"8\" y=\"200\" w=\"1\" h=\"1\" r=\"0\">"
            . "王女士" . str_repeat(" ", 4)
            . "136****3388"
            . "</TEXT>"
            . "</PAGE>";

        //第四个标签 打印条形码
        $printContent = $printContent . "<PAGE>"
            . "<TEXT x=\"8\" y=\"8\" w=\"1\" h=\"1\" r=\"0\">"
            . "打印条形码："
            . "</TEXT>"
            . "<BC128 x=\"16\" y=\"32\" h=\"32\" s=\"1\" n=\"2\" w=\"2\" r=\"0\">"
            . "12345678"
            . "</BC128>"
            . "</PAGE>";

        //第四个标签 打印二维码，宽度最小为128 低于128会无法扫描
        $printContent = $printContent . "<PAGE>"
            . "<TEXT x=\"8\" y=\"8\" w=\"1\" h=\"1\" r=\"0\">"
            . "打印二维码宽度128："
            . "</TEXT>"
            . "<QR x=\"16\" y=\"32\" w=\"128\">"
            . "https://www.xpyun.net"
            . "</QR>"
            . "</PAGE>";

        $request = new PrintRequest();
        $request->generateSign();

        //*必填*：打印机编号
        $request->sn = OK_PRINTER_SN;

        //*必填*：打印内容,不能超过12K
        $request->content = $printContent;

        //打印份数，默认为1
        $request->copies = 1;
		
		//声音播放模式，0 为取消订单模式，1 为静音模式，2 为来单播放模式，3为有用户申请退单了。默认为 2 来单播放模式
		$request->voice = 2;

        //打印模式：
        //值为 0 或不指定则会检查打印机是否在线，如果不在线 则不生成打印订单，直接返回设备不在线状态码；如果在线则生成打印订单，并返回打印订单号。
        //值为 1不检查打印机是否在线，直接生成打印订单，并返回打印订单号。如果打印机不在线，订单将缓存在打印队列中，打印机正常在线时会自动打印。
        $request->mode = 0;

        $result = $this->service->xpYunPrintLabel($request);
        print $result->content->code . "\n";
        print $result->content->msg . "\n";

        //data:正确返回订单编号
        print $result->content->data . "\n";
    }
}

?>
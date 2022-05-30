<?php

namespace App\Services;

use Exception;
use Xpyun\model\PrintRequest;
use Xpyun\service\PrintService;

class CakePrintService
{

    const USER = 'yao3060@163.com';
    const USER_KEY = 'e77d900065724915b355683868aad040';

    /**
     * 打印服务对象实例化
     */
    private PrintService $service;
    private string $user;
    private string $userKey;
    private string $printerSn;
    private string $companyName;
    private object $order;

    public function __construct($order)
    {

        $this->user = self::USER;
        $this->userKey = self::USER_KEY;

        $this->service = new PrintService();
        $this->companyName = get_option('blogname');
        $this->order = $order;

        $this->printerSn = $this->getPrinterSn();
    }

    private function getPrinterSn(): string
    {
        $userGroups = wp_get_terms_for_user(wp_get_current_user(), 'user-group');
        if (empty($userGroups)) {
            throw new Exception('用户需要绑定部门。', 403);
        }

        $printerSn = get_term_meta($userGroups[0]->term_id, 'printer_sn', true);
        if ($printerSn) {
            return $printerSn;
        }
        return  '14BMAXXC7963149';
    }

    /**
     * 小票打印综合排版样例，不支持金额播报
     * 58mm打印机一行可打印机32个字符
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
    public function printComplexReceiptWithoutBroadcast()
    {
        $request = new PrintRequest();
        $request->user = $this->user;
        $request->userKey = $this->userKey;
        $request->generateSign();

        //*必填*：打印机编号
        $request->sn = $this->printerSn;

        //*必填*：打印内容,不能超过12K
        $request->content = $this->getPrintContent();

        //声音播放模式，0 为取消订单模式，1 为静音模式，2 为来单播放模式，3为有用户申请退单了。默认为 2 来单播放模式
        $request->voice = 1;

        //打印模式：
        //值为 0 或不指定则会检查打印机是否在线，
        //  如果不在线 则不生成打印订单， 直接返回设备不在线状态码；
        //  如果在线则生成打印订单，并返回打印订单号。
        //值为 1不检查打印机是否在线，直接生成打印订单，并返回打印订单号。
        //  如果打印机不在线，订单将缓存在打印队列中，打印机正常在线时会自动打印。
        $request->mode = 1;

        $result = $this->service->xpYunPrint($request);

        return $result->content;
    }

    public function getSign(): string
    {
        return sha1($this->user . $this->userKey . $this->getMillisecond());
    }

    /**
     * 当前UNIX时间戳，精确到毫秒
     * @return string
     */
    public static function getMillisecond(): string
    {
        list($s1, $s2) = explode(' ', microtime());
        return sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * @return string
     */
    protected function getPrintContent(): string
    {
        $status = $this->order->order_status === 'trash' ? '作废单' : '配送单';
        $printContent = sprintf("<C><B2>%s (%s)</B2><BR></C>", $this->companyName, $status);
        $printContent .= "<BR>";
        $printContent .= sprintf('<B>单据：%s </B><BR>', $this->order->pickup_method);
        $printContent .= str_repeat('-', 46) . "<BR>";
        $printContent .= sprintf('<L><N>来源：%s </L></N><BR>', $this->order->order_type);
        $printContent .= sprintf('<L><N>收银：%s </L></N><BR>', UserService::getCashier((int) $this->order->creator));
        $printContent .= sprintf('<L><N>下单时间：%s </L></N><BR>', $this->order->created_at);
        $printContent .= sprintf('<L><N>订单编号：%s </L></N><BR>', $this->order->order_number);

        $printContent .= $this->billingInfo();

        $printContent .= $this->renderItemList();

        $printContent .= "<BR>";
        $printContent .= $this->shippingInfo();

        return $printContent;
    }

    protected function billingInfo()
    {
        return "";
    }

    protected function shippingInfo()
    {
        return "<L>"
            . str_repeat('-', 46) . "<BR>"
            . "配送时间：<HB>" . $this->order->pickup_time . "</HB><BR>"  //配送时间
            . "订单备注：<HB>" . $this->order->note . "</HB><BR>"
            . "收货人：<B>" .  $this->order->shipping_name . "</B><BR>"
            . "联系电话：<B>" . $this->order->shipping_phone . "</B><BR>"
            . "地址：<B>" .  $this->order->shipping_address . "</B><BR></L>"
            . "<C><QRCODE>http://weixin.qq.com/r/ZyqLkyDE9H2LrWTw9391</QRCODE></C>";
    }

    protected function renderItemList()
    {
        $printContent = str_repeat('-', 46) . "<BR>";
        $printContent .= sprintf(
            '<L><N>%s%s%s%s<BR>',
            append_spaces_to_chinese("商品名称"),
            append_spaces_to_chinese('单价', 6),
            append_spaces_to_chinese('数量', 6),
            '小计'
        );
        $totalQuantity = 0;
        foreach ($this->order->items as $item) {
            $printContent .= sprintf(
                '<L><N>%s%-6s<R>%-6d</R>%s<BR>',
                append_spaces_to_chinese($item->product_name),
                $item->price,
                $item->quantity,
                $item->total
            );
            // append product note
            $printContent .= '<L><N>备注：' . $item->note . '<BR><BR>';
            $totalQuantity += $item->quantity;
        }

        $printContent .= str_repeat('-', 46) . "<BR>";
        $printContent .= sprintf(
            '<L><N>%s%-6s<R>%-6d</R>%s<BR>',
            append_spaces_to_chinese("合计"),
            ' ',
            $totalQuantity,
            $item->total
        );

        return $printContent;
    }
}

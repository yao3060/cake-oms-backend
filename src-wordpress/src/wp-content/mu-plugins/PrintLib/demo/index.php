<?php
/**
 * 芯烨云开放平台SDK API测试样例 主入口
 */
include_once __DIR__ . '/../Autoloader.php';
include_once __DIR__ . '/XpsdkPrintApiDemo.php';
include_once __DIR__ . '/XpsdkOtherApiDemo.php';
/**
 * *必填*：开发者ID ：芯烨云后台注册账号（即邮箱地址或开发者ID），
 * 开发者用户注册成功之后，登录芯烨云后台，
 * 在【个人中心=》账号信息】下可查看开发者ID
 *
 * 当前【XXXXXXXXXXXXXXXX】只是样例，需修改再使用
 */
define('USER_NAME', 'XXXXXXXXXXXXXXXX');
/**
 * *必填*：开发者密钥 ：芯烨云后台注册账号后自动生成的开发者密钥，开发者用户注册成功之后，登录芯烨云后台，在【个人中心=》账号信息】下可查看开发者密钥
 *
 * 当前【XXXXXXXXXXXXXXXX】只是样例，需修改再使用
 */
define('USER_KEY', 'XXXXXXXXXXXXXXXX');
/**
 * *必填*：打印机设备编号，必须要在芯烨云管理后台的【打印管理->打印机管理】下添加打印机或调用API接口添加打印机，
 * 测试小票机和标签机的时候注意替换打印机编号
 * 打印机设备编号获取方式：在打印机底部会有带PID或SN字样的二维码且PID或SN后面的一串字符即为打印机编号
 *
 * 当前【XXXXXXXXXXXXXXXX】只是样例，需修改再使用
 */
define('OK_PRINTER_SN', 'XXXXXXXXXXXXXXXX');

// ###### 注意：以下接口测试样例调用，可把相关代码注释去掉即可运行，当前默认只运行小票打印机的 print 打印接口样例 ######
// 【开发者ID】和【开发者密钥】的配置以及打印机编号设置，请开发者根据自己的实际的【开发者ID】和【开发者密钥】在上面定义中修改，开发也可以定义到一个配置文件中进行处理
//###### 打印机管理接口样例 请参考【demo/examples/XpsdkOtherApiDemo.php】文件内容 begin ##############

/**
 * 打印管理样例
 */
$otherApi = new XpsdkOtherApiDemo();
/**
 * 打印测试样例
 */
$printApi = new XpsdkPrintApiDemo();

//1.批量地添加打印机
//$otherApi->addPrintersTest();

//2.设置打印机语音类型
//$otherApi->setVoiceTypeTest();

//###### 打印接口样例 请参考【demo/examples/XpsdkPrintApiDemo.php】文件内容 begin ##############
//3.小票打印字体对齐样例，不支持金额播报
//$printApi->printFontAlign();

//3.小票打印字体对齐样例，支持金额播报
//$printApi->printFontAlignVoiceSupport();

//3.小票打印综合排版样例，不支持金额播报
$printApi->printComplexReceipt();

//3.小票打印综合排版样例，支持金额播报
//$printApi->printComplexReceiptVoiceSupport();

//4.标签打印综合排版样例
//$printApi->printLabel();
//####### 打印接口样例 end ################

//5.批量删除打印机
//$otherApi->delPrintersTest();

//6.修改打印机信息
//$otherApi->updPrinterTest();

//7.清空待打印队列
//$otherApi->delPrinterQueueTest();

//8.查询订单是否打印成功
//$otherApi->queryOrderStateTest();

//9.查询指定打印机某天的订单统计数
//$otherApi->queryOrderStatisTest();

//10.获取指定打印机状态
//$otherApi->queryPrinterStatusTest();

//11.金额播报
//$otherApi->playVoiceTest();
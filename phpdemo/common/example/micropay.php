<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>FlashPay支付样例-线下支付</title>
</head>
<?php
require_once "../lib/FlashPay.Api.php";
require_once 'Log.php';

//初始化日志
$logHandler = new CLogFileHandler("../logs/" . date('Y-m-d') . '.log');
$log = Log::Init($logHandler, 15);

//打印输出数组信息
function printf_info($data)
{
    foreach ($data as $key => $value) {
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    }
}

if (isset($_REQUEST["auth_code"]) && $_REQUEST["auth_code"] != "") {
    $auth_code = $_REQUEST["auth_code"];
    $input = new FlashPayMicropayOrder();
    $input->setOrderId(FlashPayConfig::PARTNER_CODE . date("YmdHis"));
    $input->setDescription("test");
    $input->setPrice("10");
    $input->setCurrency("CAD");
    $input->setNotifyUrl("https://www.flashpayment.com//notify_url");
    $input->setDeviceId("18651874535");
    $input->setAuthCode($auth_code);
    $input->setOperator("123456");
    $currency = $input->getCurrency();
    if (!empty($currency) && $currency == 'CNY') {
        //建议缓存汇率,每天更新一次,遇节假日或其他无汇率更新情况,可取最近一个工作日的汇率
        $inputRate = new FlashPayExchangeRate();
        $rate = FlashPayApi::exchangeRate($inputRate);
        if ($rate['return_code'] == 'SUCCESS') {
            $real_pay_amt = $input->getPrice() / $rate['rate'];
            if ($real_pay_amt < 0.01) {
                echo 'CNY转换CAD后必须大于0.01CAD';
                exit();
            }
        }
    }

    //支付下单
    $result = FlashPayApi::micropayOrder($input);

    /**
     * 注意：
     * 1、提交被扫之后，返回系统繁忙、用户输入密码等错误信息时需要循环查单以确定是否支付成功
     * 2、多次（一般10次）确认都未明确成功时需要调用撤单接口撤单，防止用户重复支付
     */
    $orderInput = new FlashPayOrderQuery();
    $orderInput->setOrderId($input->getOrderId());
    for ($i = 0; $i < 10; $i++) {
        $orderResult = FlashPayApi::orderQuery($orderInput);
        if ($orderResult['result_code'] == 'PAY_SUCCESS') {
            printf_info($orderResult);
            exit();
        }
    }

    //失败退款
    $refundInput = new FlashPayApplyRefund();
    $refundInput->setOrderId($input->getOrderId());
    $refundInput->setRefundId(FlashPayConfig::PARTNER_CODE . 'REFUND' . date("YmdHis"));
    $refundInput->setFee($input->getPrice());
    printf_info(FlashPayApi::refund($refundInput));
    exit();
}

?>
<body>
<form action="#" method="post">
    <div style="margin-left:2%;">商品描述：</div>
    <br/>
    <input type="text" style="width:96%;height:35px;margin-left:2%;" readonly value="线下测试样例-支付"
           name="auth_code"/><br/><br/>
    <div style="margin-left:2%;">支付金额：</div>
    <br/>
    <input type="text" style="width:96%;height:35px;margin-left:2%;" readonly value="0.01澳元"
           name="auth_code"/><br/><br/>
    <div style="margin-left:2%;">授权码：</div>
    <br/>
    <input type="text" style="width:96%;height:35px;margin-left:2%;" name="auth_code"/><br/><br/>
    <div align="center">
        <input type="submit" value="提交支付"
               style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;"
               type="button"/>
    </div>
</form>
</body>
</html>
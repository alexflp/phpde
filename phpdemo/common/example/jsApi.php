<?php
ini_set('date.timezone', 'America/Vancouver');
error_reporting(E_ERROR);
require_once "../lib/FlashPay.Api.php";
require_once 'Log.php';
header("Content-Type:text/html;charset=utf-8");

//初始化日志
$logHandler = new CLogFileHandler("../logs/" . date('Y-m-d') . '.log');
$log = Log::Init($logHandler, 15);

$input = new FlashPayUnifiedOrder();
$input->setOrderId(FlashPayConfig::PARTNER_CODE . date("YmdHis"));
$input->setDescription("test");
$input->setPrice("1");
$input->setCurrency("CAD");
$input->setNotifyUrl("https://www.flashpayment.com//notify_url");
$input->setOperator("123456");
$currency = $input->getCurrency();
if (!empty($currency) && $currency == 'CNY') {
    //建议缓存汇率,每天更新一次,遇节假日或其他无汇率更新情况,可取最近一个工作日的汇率
    $inputRate = new FlashPayExchangeRate();
    $rate = FlashPayApi::exchangeRate($inputRate);
    if ($rate['return_code'] == 'SUCCESS') {
        $real_pay_amt = $input->getPrice() / $rate['rate'] / 100;
        if ($real_pay_amt < 0.01) { 
            echo 'CNY转换CAD后必须大于0.01CNY';
            exit();
        }
    }
}
//支付下单
$result = FlashPayApi::jsApiOrder($input);

//跳转
$inputObj = new FlashPayJsApiRedirect();
$inputObj->setDirectPay('true');
$inputObj->setRedirect(urlencode('http://www.flashpayment.com?order_id=' . strval($input->getOrderId())));
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>FlashPay支付样例-jsApi支付</title>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</head>
<body>
<br/>
<font color="#9ACD32"><b>(*需要在微信客户端打开）该笔订单支付金额为<span style="color:#f00;font-size:50px">0.01</span>CAD</b></font><br/><br/>
<div align="center">
    <button
        style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;"
        type="button"
        onclick="redirect('<?php echo FlashPayApi::getJsApiRedirectUrl($result['pay_url'], $inputObj); ?>')">
        立即支付
    </button>
</div>
</body>
</html>
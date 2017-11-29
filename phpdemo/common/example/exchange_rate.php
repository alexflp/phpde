<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>FlashPay支付样例-汇率查询</title>
</head>
<body>
<?php
ini_set('date.timezone', 'America/Vancouver');
require_once "../lib/FlashPay.Api.php";
require_once "../lib/FlashPay.Data.php";

function printf_info($data)
{
    foreach ($data as $key => $value) {
        echo "<font color='#f00;'>$key</font> : $value <br/>";
    }
}

$input = new FlashPayExchangeRate();
printf_info(FlashPayApi::exchangeRate($input));
exit();
?>
</body>
</html>
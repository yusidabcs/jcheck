<?php
$BILLHOST_URL = 'http://demos.finnet-indonesia.com/195/';
//$BILLHOST_URL = 'https://billhosting.finnet-indonesia.com/prepaidsystem/195/';
$con =  array(
	'FinPay' => 
	[
		'merchant_id' => 'JARVIS131',
		'merchant_password' => 'JARVIS131#0603',
		'merchant_type' => 'PAYMENT',
		'RETURN_URL_195' => 'http://jarvis-store.com/mycheckout/finpay/response',
		'BILLHOST_URL' => $BILLHOST_URL,
		'REQUEST_URL_195' => $BILLHOST_URL.'response-insert.php',
		'CHECK_STATUS_URL_195' => $BILLHOST_URL.'check-status.php',
		'CANCEL_URL_195' => $BILLHOST_URL.'cancel-transaction.php'
	]
);
return $con;
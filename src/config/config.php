<?php

return array(
	'FinPay' => array(
		'merchant_id' => 'JARVIS131',
		'merchant_password' => 'JARVIS131#0603',
		'merchant_type' => 'PAYMENT',
		'RETURN_URL_195' => 'http://jarvis-store.com/payment/195.return_url.php',
		'BILLHOST_URL' => 'http://demos.finnet-indonesia.com/195/',
		//'BILLHOST_URL' => 'https://billhosting.finnet-indonesia.com/prepaidsystem/195/',
		'REQUEST_URL_195' => Config::get('jchecout::FinPay.BILLHOST_URL').'response-insert.php',
		'CHECK_STATUS_URL_195' => Config::get('jchecout::FinPay.BILLHOST_URL').'check-status.php',
		'CANCEL_URL_195' => Config::get('jchecout::FinPay.BILLHOST_URL').'cancel-transaction.php'
	)
);
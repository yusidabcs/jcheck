<html>
<head>
<title>Finnet 195 Merchant Simulator</title>
</head>
<body>
<h1>Finnet 195 Merchant Simulator</h1>
<table border="1">
<tr valign="top"><td>
	<?php
	$invoice = rand("10000000","99999999");
	?>
	<h2>1. Request</h2>
	<form method="post" action="{{URL::to('mycheckout/finpay/create')}}">
	<input type="hidden" name="action" value="195_REQUEST" />
	<table>
		<tr><td>Invoice</td><td><input type="text" name="invoice" value="<?php echo $invoice; ?>"></td></tr>
		<tr><td>Amount</td><td><input type="text" name="amount" value="1000"></td></tr>
		<tr><td>Add Info 1</td><td><input type="text" name="add_info1" value="<?php echo "INVOICE ".$invoice; ?>"></td></tr>
		<?php
		for($i=2;$i<=5;$i++){
		echo '
		<tr><td>Add Info '.$i.'</td><td><input type="text" name="add_info'.$i.'"></td></tr>';
		}
		?>
		<tr><td>&nbsp;</td><td><input type="submit" value="SEND"></td></tr>
	</table>
	{{{$message}}}
	</form>
</td><td>
	<h2>2. Check Status</h2>
	<form method="post" action="{{URL::to('mycheckout/finpay/check')}}">
	<input type="hidden" name="action" value="195_CHECK_STATUS" />
	<table>
		<tr><td>195 Payment Code</td><td><input type="text" name="payment_code" value="0195"></td></tr>
		<tr><td>&nbsp;</td><td><input type="submit" value="SEND"></td></tr>
	</table>
	{{{$message}}}
	</form>
</td><td>
	<h2>3. Cancel</h2>
	<form method="post" action="{{URL::to('mycheckout/finpay/cancel')}}">
	<input type="hidden" name="action" value="195_CANCEL_TRANSACTION" />
	<table>
		<tr><td>195 Payment Code</td><td><input type="text" name="payment_code" value="0195"></td></tr>
		<tr><td>&nbsp;</td><td><input type="submit" value="SEND"></td></tr>
	</table>
	{{{$message}}}
	</form>
</td><td>
	<h2>4. Payment Simulator</h2>
	<iframe src="http://demos.finnet-indonesia.com/195/payment-process.php" style="width:350px; height:200px;"></iframe>
	<br /><font color="red">NOTE : JUST WORK IN DEVELOPMENT HOST</font>
</td></tr>
</table>
</body>
</html>
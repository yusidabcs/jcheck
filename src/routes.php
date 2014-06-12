<?php

Route::group(array('prefix' => 'mycheckout'), function()
{
	Route::group(array('before'=>'subdomain'), function()

	{
		Route::get('','Bcscoder\Jcheckout\JcheckoutMainController@getIndex');
		Route::post('pengiriman','Bcscoder\Jcheckout\JcheckoutMainController@getPengiriman');
		Route::get('pengiriman','Bcscoder\Jcheckout\JcheckoutMainController@getPengiriman');
		Route::get('pembayaran','Bcscoder\Jcheckout\JcheckoutMainController@getPembayaran');
		Route::post('pembayaran','Bcscoder\Jcheckout\JcheckoutMainController@getPembayaran');
		Route::get('konfirmasi','Bcscoder\Jcheckout\JcheckoutMainController@getKonfirmasi');
		Route::post('konfirmasi','Bcscoder\Jcheckout\JcheckoutMainController@getKonfirmasi');
		Route::post('finish','Bcscoder\Jcheckout\JcheckoutMainController@getFinish');
		Route::get('finish','Bcscoder\Jcheckout\JcheckoutMainController@getFinish');
		Route::get('bantuan','Bcscoder\Jcheckout\JcheckoutMainController@getBantuan');


		Route::get('provinsi/{id}','Bcscoder\Jcheckout\JcheckoutCartController@getProvinsi');
		Route::get('kabupaten/{id}','Bcscoder\Jcheckout\JcheckoutCartController@getKabupaten');
		Route::resource('/cart','Bcscoder\Jcheckout\JcheckoutCartController');
		Route::get('cart/checkdiskon/{any}', 'Bcscoder\Jcheckout\JcheckoutCartController@checkdiskon');
		Route::get('cart/checkekspedisi/{any}', 'Bcscoder\Jcheckout\JcheckoutCartController@checkekspedisi');
		Route::get('cart/addekspedisi/{any}', 'Bcscoder\Jcheckout\JcheckoutCartController@addekspedisi');
		Route::get('cart/delete/{any}', 'Bcscoder\Jcheckout\JcheckoutCartController@delete');

		Route::resource('konfirmasi-order','Bcscoder\Jcheckout\JcheckoutOrderController');

		Route::group(array('prefix' => 'finpay'), function()
		{
			Route::get('/','Bcscoder\Jcheckout\JcheckoutMainController@finpayDemo');
			Route::post('/create','Bcscoder\Jcheckout\JcheckoutMainController@createFinPay');
			Route::post('/check','Bcscoder\Jcheckout\JcheckoutMainController@checkFinPay');
			Route::post('/cancel','Bcscoder\Jcheckout\JcheckoutMainController@cancelFinPay');

		});
	});
	Route::group(array('prefix' => 'finpay'), function()
	{
		Route::post('response',function(){
			//IMPORTANT!! This is to tell the engine 195 that the sent data has been accepted by the merchant
			//PENTING!! Ini adalah untuk memberitahu mesin 195 bahwa data yang dikirim telah diterima oleh pedagang
			echo '00';

			//TO WRITE LOG POST FROM 195 RESPON
			$log = '';
			foreach($_POST as $name=>$value){
				$_POST[$name]=htmlspecialchars(strip_tags(trim($value)));
			$log .= $name.' : '.htmlspecialchars(strip_tags(trim($value))).'
			';
			}

			//EXTRACT POST TO VARIABLE
			extract($_POST);

			//REQEUST CODE 195
			if($_POST["trax_type"]=="195Code"){
				$log = 'RESPON REQUEST '.date("Y-m-d h:i:s").' ENGINE 195'.$log;
				writeLog($log);
				$mer_signature = $_POST["mer_signature"];
				unset($_POST["mer_signature"]);
				unset($_POST["amount"]);
				unset($_POST["paid"]);
				writeLog(check_mer_signature($mer_signature,$_POST,\Config::get('jcheckout::FinPay.merchant_password')));
				if(check_mer_signature($mer_signature,$_POST,\Config::get('jcheckout::FinPay.merchant_password'))){ //SECURE DATA
					//DO ACTION WITH YOUR CONDITION
					$payment = Bcscoder\Jcheckout\FinPay::where('invoice','=',$invoice)->first();
					$payment->trax_type = $trax_type;
					$payment->payment_code = $payment_code;
					$payment->save();
				}
			}

			//PAYMENT SUCCESS RESULT CODE 00
			if($_POST["trax_type"]=="Payment" and $_POST["result_code"]=="00" ){
				$log = 'RESPON PAYMENT SUCCESS '.date("Y-m-d h:i:s").' ENGINE 195'.$log;
				writeLog($log);
				$mer_signature = $_POST["mer_signature"];
				unset($_POST["mer_signature"]);
				unset($_POST["amount"]);
				unset($_POST["paid"]);
				if(check_mer_signature($mer_signature,$_POST,\Config::get('jcheckout::FinPay.merchant_password'))){ //SECURE DATA
					//DO ACTION WITH YOUR CONDITION
					$payment = Bcscoder\Jcheckout\FinPay::where('payment_code','=',$payment_code)->first();
					$payment->trax_type = $trax_type;
					$payment->result_code = $result_code;
					$payment->result_desc = $result_desc;
					$payment->log_no = $log_no;
					$payment->payment_source = $payment_source;
					$payment->save();

					//Update Order
					$order = $payment->order();
					$order->status = 2;
					$order->save();

					$toko = \Pengaturan::remember(1)->where('akunId','=',$order->akunId)->first();
					$data = array(
			            'pelanggan'=> $order->nama,
			            'pelangganalamat'=> $order->alamat,
			            'pelangganphone'=> $order->telp,
			            'toko' => $toko->nama,
			            'kodeorder' => $order->kodeOrder,
			            'tanggal' => $order->tanggalOrder,
			            'namaPengirim' => $order->konfirmasi==null ? '-':$order->konfirmasi->nama,
			            'noRekening' => $order->konfirmasi==null ? '-':$order->konfirmasi->noRekPengirim,
			            'rekeningTujuan' =>$order->konfirmasi==null ? '-' : $order->konfirmasi->bank->atasNama.'<br>'.$order->konfirmasi->bank->noRekening.' - '.$order->konfirmasi->bank->bankdefault->nama,
			            'jumlah' =>$order->konfirmasi==null ? '-':price_format($order->konfirmasi->jumlah),
			            'cart' => \View::make('admin.order.detailorder')->with('order',$order),
			            'namaEkspedisi' => $order->jenisPengiriman,
			            'noResi' => $order->noResi,
			            'tujuanPengiriman' => $order->alamat.' - '.$order->kota,
			            'linkRegistrasi' => \URL::to('member/create'),
			            'kode_pembayaran' => $payment_code
			            );
					//kirim email pembyaran diterima
					$template_email = \Templateemail::where('akunId','=',$order->akunId)->where('no','=',6)->first();
					$template = \View::make('jcheckout::email.konfirmasi');
		            $email = bind_to_template($data,$template);            
		            $subject = bind_to_template($data,$template_email->judul);  
		            $a = \Mail::send('jcheckout::email.send',array('data'=>$email), function($message) use ($subject,$order,$toko)
		            {   
		                $message->from($toko->email, $toko->nama);
		                $message->to($order->pelanggan->email, $order->pelanggan->nama)->subject($subject);
		            });

		            //kirim email pembyaran diterima
		            $subject = 'Konfirmasi Order - '.$subject;  
		            $a = \Mail::send('jcheckout::email.send',array('data'=>$email), function($message) use ($subject,$order,$toko)
		            {   
		                $message->to($toko->email, $toko->nama)->subject($subject);
		            });  

				}
			}

			//PAYMENT EXPIRED RESULT CODE 05
			if($_POST["trax_type"]=="Payment" and $_POST["result_code"]=="05" ){
				$log = 'RESPON PAYMENT EXPIRED  '.date("Y-m-d h:i:s").' ENGINE 195 '.$log;
				writeLog($log);
				$mer_signature = $_POST["mer_signature"];
				unset($_POST["mer_signature"]);
				unset($_POST["amount"]);
				unset($_POST["paid"]);
				if(check_mer_signature($mer_signature,$_POST,\Config::get('jcheckout::FinPay.merchant_password'))){ //SECURE DATA
					//DO ACTION WITH YOUR CONDITION
					$payment = Bcscoder\Jcheckout\FinPay::where('payment_code','=',$payment_code)->first();
					$payment->trax_type = $trax_type;
					$payment->result_code = $result_code;
					$payment->result_desc = $result_desc;
					$payment->log_no = $log_no;
					$payment->payment_source = $payment_source;
					$payment->save();
				}
			}

			//REQUEST CANCEL TRANSACTION
			if($_POST["trax_type"]=="Cancel"){
				$log = '
			RESPON CANCEL TRANSACTION '.date("Y-m-d h:i:s").' ENGINE 195
			'.$log;
				writeLog($log);
				$mer_signature = $_POST["mer_signature"];
				unset($_POST["mer_signature"]);
				if(check_mer_signature($mer_signature,$_POST,\Config::get('jcheckout::FinPay.merchant_password'))){ //SECURE DATA
					//DO ACTION WITH YOUR CONDITION
					if($result_code=='00'){
						$payment = Bcscoder\Jcheckout\FinPay::where('payment_code','=',$payment_code)->first();
						$payment->trax_type = $trax_type;
						$payment->result_code = $result_code;
						$payment->result_desc = $result_desc;
						$payment->save();
					}else{
						$payment = Bcscoder\Jcheckout\FinPay::where('payment_code','=',$payment_code)->first();
						$payment->result_desc = $result_desc;
						$payment->save();
					}
				}
			}

			//CHECK STATUS TRANSACTION
			if($_POST["trax_type"]=="195Status"){
				$log = '
			CHECK STATUS '.date("Y-m-d h:i:s").' ENGINE 195
			'.$log;
				writeLog($log);
				$mer_signature = $_POST["mer_signature"];
				unset($_POST["mer_signature"]);
				unset($_POST["amount"]);
				unset($_POST["paid"]);
				if(check_mer_signature($mer_signature,$_POST,\Config::get('jcheckout::FinPay.merchant_password'))){ //SECURE DATA
					if($_POST["result_code"]=="00"){ //PAID
						//DO ACTION WITH YOUR CONDITION
					}else if($_POST["result_code"]=="04"){ //UNPAID
						//DO ACTION WITH YOUR CONDITION
					}else if($_POST["result_code"]=="05"){ //EXPIRED
						//DO ACTION WITH YOUR CONDITION
					}else if($_POST["result_code"]=="06"){ //CANCEL
						//DO ACTION WITH YOUR CONDITION
					}else if($_POST["result_code"]=="14"){ //NOT FOUND
						//DO ACTION WITH YOUR CONDITION
					}
				}
			}
		});
		Route::get('response',function(){
			//IMPORTANT!! This is to tell the engine 195 that the sent data has been accepted by the merchant
			//PENTING!! Ini adalah untuk memberitahu mesin 195 bahwa data yang dikirim telah diterima oleh pedagang
			echo '00';

			//TO WRITE LOG POST FROM 195 RESPON
			$log = '';
			foreach($_POST as $name=>$value){
				$_POST[$name]=htmlspecialchars(strip_tags(trim($value)));
			$log .= $name.' : '.htmlspecialchars(strip_tags(trim($value))).'
			';
			}

			//EXTRACT POST TO VARIABLE
			extract($_POST);

			//REQEUST CODE 195
			if($_POST["trax_type"]=="195Code"){
				$log = 'RESPON REQUEST '.date("Y-m-d h:i:s").' ENGINE 195'.$log;
				writeLog($log);
				$mer_signature = $_POST["mer_signature"];
				unset($_POST["mer_signature"]);
				unset($_POST["amount"]);
				unset($_POST["paid"]);
				if(check_mer_signature($mer_signature,$_POST,\Config::get('jcheckout::FinPay.merchant_password'))){ //SECURE DATA
					//DO ACTION WITH YOUR CONDITION
					$payment = FinPay::where('invoice','=',$invoice)->first();
					$payment->trax_type = $trax_type;
					$payment->payment_code = $payment_code;
					$payment->save();
				}
			}

			//PAYMENT SUCCESS RESULT CODE 00
			if($_POST["trax_type"]=="Payment" and $_POST["result_code"]=="00" ){
				$log = 'RESPON PAYMENT SUCCESS '.date("Y-m-d h:i:s").' ENGINE 195'.$log;
				writeLog($log);
				$mer_signature = $_POST["mer_signature"];
				unset($_POST["mer_signature"]);
				unset($_POST["amount"]);
				unset($_POST["paid"]);
				if(check_mer_signature($mer_signature,$_POST,\Config::get('jcheckout::FinPay.merchant_password'))){ //SECURE DATA
					//DO ACTION WITH YOUR CONDITION
					$payment = FinPay::where('payment_code','=',$payment_code)->first();
					$payment->trax_type = $trax_type;
					$payment->result_code = $result_code;
					$payment->result_desc = $result_desc;
					$payment->log_no = $log_no;
					$payment->payment_source = $payment_source;
					$payment->save();
				}
			}

			//PAYMENT EXPIRED RESULT CODE 05
			if($_POST["trax_type"]=="Payment" and $_POST["result_code"]=="05" ){
				$log = 'RESPON PAYMENT EXPIRED  '.date("Y-m-d h:i:s").' ENGINE 195 '.$log;
				writeLog($log);
				$mer_signature = $_POST["mer_signature"];
				unset($_POST["mer_signature"]);
				unset($_POST["amount"]);
				unset($_POST["paid"]);
				if(check_mer_signature($mer_signature,$_POST,\Config::get('jcheckout::FinPay.merchant_password'))){ //SECURE DATA
					//DO ACTION WITH YOUR CONDITION
					$payment = FinPay::where('payment_code','=',$payment_code)->first();
					$payment->trax_type = $trax_type;
					$payment->result_code = $result_code;
					$payment->result_desc = $result_desc;
					$payment->log_no = $log_no;
					$payment->payment_source = $payment_source;
					$payment->save();
				}
			}

			//REQUEST CANCEL TRANSACTION
			if($_POST["trax_type"]=="Cancel"){
				$log = '
			RESPON CANCEL TRANSACTION '.date("Y-m-d h:i:s").' ENGINE 195
			'.$log;
				writeLog($log);
				$mer_signature = $_POST["mer_signature"];
				unset($_POST["mer_signature"]);
				if(check_mer_signature($mer_signature,$_POST,\Config::get('jcheckout::FinPay.merchant_password'))){ //SECURE DATA
					//DO ACTION WITH YOUR CONDITION
					if($result_code=='00'){
						$payment = FinPay::where('payment_code','=',$payment_code)->first();
						$payment->trax_type = $trax_type;
						$payment->result_code = $result_code;
						$payment->result_desc = $result_desc;
						$payment->save();
					}else{
						$payment = FinPay::where('payment_code','=',$payment_code)->first();
						$payment->result_desc = $result_desc;
						$payment->save();
					}
				}
			}

			//CHECK STATUS TRANSACTION
			if($_POST["trax_type"]=="195Status"){
				$log = '
			CHECK STATUS '.date("Y-m-d h:i:s").' ENGINE 195
			'.$log;
				writeLog($log);
				$mer_signature = $_POST["mer_signature"];
				unset($_POST["mer_signature"]);
				unset($_POST["amount"]);
				unset($_POST["paid"]);
				if(check_mer_signature($mer_signature,$_POST,\Config::get('jcheckout::FinPay.merchant_password'))){ //SECURE DATA
					if($_POST["result_code"]=="00"){ //PAID
						//DO ACTION WITH YOUR CONDITION
					}else if($_POST["result_code"]=="04"){ //UNPAID
						//DO ACTION WITH YOUR CONDITION
					}else if($_POST["result_code"]=="05"){ //EXPIRED
						//DO ACTION WITH YOUR CONDITION
					}else if($_POST["result_code"]=="06"){ //CANCEL
						//DO ACTION WITH YOUR CONDITION
					}else if($_POST["result_code"]=="14"){ //NOT FOUND
						//DO ACTION WITH YOUR CONDITION
					}
				}
			}
		});
	});
	

});

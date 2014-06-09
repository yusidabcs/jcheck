<?php

Route::group(array('prefix' => 'mycheckout','before'=>'subdomain'), function()
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


		Route::get('provinsi/{id}','Bcscoder\Jcheckout\JcheckoutCartController@getProvinsi');
		Route::get('kabupaten/{id}','Bcscoder\Jcheckout\JcheckoutCartController@getKabupaten');
		Route::resource('/cart','Bcscoder\Jcheckout\JcheckoutCartController');
		Route::get('cart/checkdiskon/{any}', 'Bcscoder\Jcheckout\JcheckoutCartController@checkdiskon');
		Route::get('cart/checkekspedisi/{any}', 'Bcscoder\Jcheckout\JcheckoutCartController@checkekspedisi');
		Route::get('cart/addekspedisi/{any}', 'Bcscoder\Jcheckout\JcheckoutCartController@addekspedisi');
		Route::get('cart/delete/{any}', 'Bcscoder\Jcheckout\JcheckoutCartController@delete');

		Route::group(array('prefix' => 'finpay'), function()
		{
			Route::get('/','Bcscoder\Jcheckout\JcheckoutMainController@finpayDemo');
			Route::post('/create','Bcscoder\Jcheckout\JcheckoutMainController@createFinPay');
			Route::post('/check','Bcscoder\Jcheckout\JcheckoutMainController@checkFinPay');
			Route::post('/cancel','Bcscoder\Jcheckout\JcheckoutMainController@finpayDemo');

		});
	});
	Route::group(array('prefix' => 'finpay'), function()
	{
		Route::get('response','Bcscoder\Jcheckout\JcheckoutMainController@getFinPayResponse');
	});
	
	
	

	Route::get('tes',function(){
		$tes = Bcscoder\Jcheckout\FinPay::find(1);
		return $tes->order();
	});
});

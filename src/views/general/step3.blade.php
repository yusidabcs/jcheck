@section('content')
<div id="demos">
	<h1 class="text-center">Checkout - Metode Pembayaran</h1>
	<hr>
	<div id="psteps_horiz_layout" class="pf-form">
		<div class="row-fluid">
			<div>
				<div class="step-title btn span3"><a href="{{URL::to('mycheckout')}}" data-pjax><span class="step-order">1.</span> <span class="step-name hidden-phone">Rincian Belanja</span></a></div>
                <div class="step-title btn span3"><a href="{{URL::to('mycheckout/pengiriman')}}" ><span class="step-order">2.</span> <span class="step-name">Data Pembeli</span></a></div>
                <div class="step-title btn btn-success span3"><span class="step-order">3.</span> <span class="step-name hidden-phone">Metode Pembayaran</span></div>
                <div class="step-title btn disabled span3"><span class="step-order">4.</span> <span class="step-name hidden-phone">Ringkasan Order</span></div>
				
			</div>
		</div>
		<div class="row-fluid box">
			<div class="span12 box-content">
				<form class="form-horizontal" action="{{URL::to('mycheckout/konfirmasi')}}" name='pembayaran' method='post' data-pjax>
				<div class="step-content">
					<div class="row-fluid">
                       <div class="span4">
                       	Pilih Salah Satu Jenis Pembayaran: <br><br>
						<label class="radio">
							<input type="radio" name="tipepembayaran" id="optionsRadios1" value="bank" {{$pembayaran!=null? ($pembayaran['tipepembayaran']=='bank'?'checked':''):'' }}>
							  Transfer Bank<br>
						</label><br><br>
						@if($paypal->aktif)
						<label class="radio">
						  <input type="radio" name="tipepembayaran" id="optionsRadios2" value="paypal" {{$pembayaran!=null? ($pembayaran['tipepembayaran']=='paypal'?'checked':''):'' }}>
						  Paypal
						</label><br><br>
						@endif
						@if($creditcard->aktif)
						<label class="radio">
						  <input type="radio" name="tipepembayaran" id="optionsRadios2" value="creditcard" {{$pembayaran!=null? ($pembayaran['tipepembayaran']=='creditcard'?'checked':''):'' }}>
						  Kartu Kredit
						</label><br><br>
						@endif
						@if(@$ipaymu->aktif)
						<label class="radio">
						  <input type="radio" name="tipepembayaran" id="optionsRadios3" value="ipaymu" {{$pembayaran!=null? ($pembayaran['tipepembayaran']=='ipaymu'?'checked':''):'' }}>
						  iPaymu
						</label>
						@endif

						<label class="radio">
							<input type="radio" name="tipepembayaran" id="optionsRadios1" value="jarvis_payment" {{$pembayaran!=null? ($pembayaran['tipepembayaran']=='jarvis_payment'?'checked':''):'' }}>
							  Jarvis Payment [beta]<br>
						</label><br><br>
                       </div>
                       <div class="span8">
                       		<div class="well" style="display:none" id="bank">
                       			<table class="table table-striped">
								  <thead>
									  <tr>
										  <th>Bank</th>
										  <th>No. Rekening</th>
										  <th>Atas Nama</th>                                       
									  </tr>
								  </thead>   
								  <tbody>
								  	@foreach($banktrans as $key =>$banktran)
									<tr>
										<td class="center">
											@foreach($banks as $key => $logoBank)
												@if($banktran->bankDefaultId==$logoBank->id)
													<img src="{{URL::to('img/'.$logoBank->logo)}}" width="80">
												@endif
											@endforeach
										</td>
										<td class="center">{{$banktran->noRekening}}</td>
										<td class="center">{{$banktran->atasNama}}</td>                   
									</tr>
									@endforeach
								  </tbody>
							 </table>
                       		</div>
                       		@if($paypal->aktif)
							<div class="well" style="display:none" id="paypal">
								<p>Silakan melakukan pembayaran dengan paypal Anda secara online via paypal payment gateway. Transaksi ini berlaku jika pembayaran dilakukan maximal (2x24 jam). </p>
                       		</div>
							@endif
							@if($creditcard->aktif)
							<div class="well" style="display:none" id="creditcard">
                       		</div>
							@endif
							@if(@$ipaymu->aktif)
							<div class="well" style="display:none" id="ipaymu">
                       		</div>
							@endif

							<div class="well" style="display:none" id="jarvis_payment">
                       			<h4 class="text-success">Silakan pilih salah satu tipe pembayaran</h4>
                       			<hr>
                       			<div class="row-fluid">
                       				<div class="span6">
                       					<label class="radio">
											<input type="radio" name="jarvis_payment_type" id="optionsRadios1" value="credit_card" {{$pembayaran!=null? ($pembayaran['jarvis_payment_type']=='credit_card'?'checked':''):'' }}>
											  Credit Card
										</label>
                       				</div>
                       				<div class="span6">
                       					<img src="{{URL::to('packages/bcscoder/jcheckout/img/visa_mastercard_logo.gif')}}" style="height:25px">
                       				</div>
                       			</div>
                       			<hr>
                       			<div class="row-fluid">
                       				<div class="span6">
                       					<label class="radio">
											<input type="radio" name="jarvis_payment_type" id="optionsRadios1" value="bank_channel" {{$pembayaran!=null? ($pembayaran['jarvis_payment_type']=='bank_channel'?'checked':''):'' }}>
											  Bank Channel <i class="icon-question-sign" ></i>
										</label>
                       				</div>
                       				<div class="span6">
                       					<img src="{{URL::to('packages/bcscoder/jcheckout/img/bank/bca.png')}}" style="height:25px;width:75px;margin-bottom:5px">
                       					<img src="{{URL::to('packages/bcscoder/jcheckout/img/bank/bri.png')}}" style="height:25px;width:75px;margin-bottom:5px">
                       					<img src="{{URL::to('packages/bcscoder/jcheckout/img/bank/bni.png')}}" style="height:25px;width:75px;margin-bottom:5px">
                       					<img src="{{URL::to('packages/bcscoder/jcheckout/img/bank/bii.png')}}" style="height:25px;width:75px;margin-bottom:5px">
                       					<img src="{{URL::to('packages/bcscoder/jcheckout/img/bank/cimb.png')}}" style="height:25px;width:75px;margin-bottom:5px">
                       					<img src="{{URL::to('packages/bcscoder/jcheckout/img/bank/citibank.png')}}" style="height:25px;width:75px;margin-bottom:5px">
                       					<img src="{{URL::to('packages/bcscoder/jcheckout/img/bank/danamon.png')}}" style="height:25px;width:75px;margin-bottom:5px">
                       					<img src="{{URL::to('packages/bcscoder/jcheckout/img/bank/hsbc.png')}}" style="height:25px;width:75px;margin-bottom:5px">
                       					<img src="{{URL::to('packages/bcscoder/jcheckout/img/bank/mandiri.png')}}" style="height:25px;width:75px;margin-bottom:5px">
                       					<img src="{{URL::to('packages/bcscoder/jcheckout/img/bank/permata.png')}}" style="height:25px;width:75px;margin-bottom:5px">
                       				</div>
                       			</div>
                       		</div>
                       </div>
                    </div>					
				</div>

				<div style="clear:both;"></div>
				<button class="next-button btn btn-info" type="submit">Lanjut <i class="icon-arrow-right"></i></button>
				<a href="{{URL::to('mycheckout/pengiriman')}}" data-pjax class="back-button btn btn-warning"><i class="icon-arrow-left"></i> Kembali</a
				</form>
			</div>
		</div>
	</div>

</div>
@endsection
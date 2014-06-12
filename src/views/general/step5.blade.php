@section('content')

<div id="demos">
	<h1 class="text-center">Finish - Informasi Order</h2>
	<hr>
	<div id="psteps_horiz_layout" class="pf-form">
		<div class="row-fluid box">
			<div class="span12 box-content">
				<div class="span12">
						<div class="well">
							Terima Kasih {{$datapengirim['nama']}} telah berbelanja dengan kami.
							<br>
							<h3>ID ORDER: {{$order->kodeOrder}}</h3>Total Harga Belanjaan: {{jadiRupiah($order->total)}}<hr>
							Data pesanan Anda telah berhasil dikirimkan. Sebuah email, yang berisikan informasi pesanan ini dan tahap selanjutnya yang harus dilakukan, telah dikirimkan ke alamat email Anda.
						</div>
				</div>
														
				</div>
			</div>
		</div>
		<div class="row-fluid box">
			<div class="span12 box-content">
				@if($order->jenisPembayaran==1)
					<div class="span12">
						<div class="well">
							<!-- pembayaran via transfer bank -->
							Silahkan anda melakukan pembayaran kesalah satu rekening berikut
							<br>

							<table class="table">
								<tr>
									<td class="center">
									Bank
									</td>
									<td class="center">No rekening</td>
									<td class="center">Atas Nama</td>                   
								</tr>
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
							</table>
							<hr>
							<p>Setelah melakukan pembayaran anda bisa mengkonfirmasi pembayaran anda disini:</p>
							<a href="{{URL::to('mycheckout/konfirmasi-order/'.$order->id)}}" class="btn theme">Konfirmasi Pembayaran</a>
						</div>
					</div>
				@endif
				@if($order->jenisPembayaran==2)
					<div class="span12">
						<div class="well">
							<!-- pembayaran via paypal -->
							<p>Silakan melakukan pembayaran dengan paypal Anda secara online via paypal payment gateway. Transaksi ini berlaku jika pembayaran dilakukan sebelum 02 Jul 2013 pukul 17:26 WIB (2x24 jam). Klik tombol "Bayar Dengan Paypal" di bawah untuk melanjutkan proses pembayaran.</p>
							{{$paypalbutton}}
						</div>
					</div>
				@endif
				@if($order->jenisPembayaran==3)
					Via Credit Card
				@endif	
				@if($order->jenisPembayaran==4)
					<p>Silakan melakukan pembayaran dengan iPaymu.Klik tombol "iPaymu" di bawah untuk melanjutkan proses pembayaran.</p>
					<a class="btn btn-info" href="{{URL::to('ipaymu/'.$order->id)}}" target="_blank">Bayar dengan iPaymu</a>
				@endif

				@if($order->jenisPembayaran==6)
				<h3>Pembayaran : Jarvis Payment - Bank Channel </h3>
				<p>Silakan melakukan pembayaran melalui bank channel yang kami sediakan (ATM/I-banking). Pembayaran paling lambat dilakukan setelah 1x12 jam.</p>
				<center>
				<h4>Kode Pembayaran anda<br>
				<?php
				$payment = Bcscoder\Jcheckout\FinPay::where('invoice','=',$order->kodeOrder)->first();
				?>
				{{$payment->payment_code}}
				</h4>
				</center>
				@endif
				@if($order->jenisPembayaran==5)
				<h3>Pembayaran : Jarvis Payment - Credit Card </h3>
				@endif
			</div>
		</div>
		</div>

	</div>
	@endsection
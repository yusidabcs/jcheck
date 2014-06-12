@section('content')
@if($errors->all())
<div class="error" id='message' style='display:none'>
    @foreach($errors->all() as $message)
    {{ $message }}<br>
    @endforeach
</div>
@endif
@if(Session::has('success'))
<div class="success" id='message' style='display:none'>
    <p>Terima kasih, konfirmasi anda sudah terkirim.</p>
</div>
@endif

@if(Session::has('message'))
<div class="error" id='message' style='display:none'>
    <p>Maaf, kode order anda tidak ditemukan.</p>                   
</div>      
@endif
@if(Session::has('error'))
<div class="error" id='message' style='display:none'>
    <p>Maaf, kode order anda tidak ditemukan.</p>                   
</div>      
@endif
<div id="demos">
    <h1 class="text-center">Checkout - Konfirmasi Order</h1>
    <hr>
    <div id="psteps_horiz_layout" class="pf-form">
        <hr>
        <div class="row-fluid box">
            <div class="span12 box-content" style="border:none">
                <p class="text-center">
                    Silakan masukkan kode order untuk mencari order anda!
                </p>
                <center>
                    <form class="form-search" action="{{URL::to('mycheckout/konfirmasi-order')}}" method="post">
                      <input type="text" class="input-large search-query" name="kode_order" value="{{isset($order)?$order->kodeOrder:''}}">
                      <button type="submit" class="btn">Cari</button>
                    </form>
                </center>

                @if(isset($order))
                <hr>
                <table class="table table-striped">
                    <tr>
                        <th>Kode Order</th>
                        <th>Tanggal Order</th>
                        <th>Jumlah</th>
                        <th>Jenis Pembayaran</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    <tr>
                        <td>{{$order->kodeOrder}}</td>
                        <td>{{date("d F Y",strtotime($order->tanggalOrder)).' '.date("g:ha",strtotime($order->tanggalOrder))}}</td>
                        <td>{{price_format($order->total)}}</td>
                        <td>
                            @if($order->jenisPembayaran==1)
                                <span class="label">Transfer Bank</span>
                            @endif
                            @if($order->jenisPembayaran==2)
                                <span class="label">Paypal</span>
                            @endif
                            @if($order->jenisPembayaran==3)
                                <span class="label">Credit Card</span>
                            @endif
                            @if($order->jenisPembayaran==4)
                                <span class="label">Ipaymu</span>
                            @endif
                            @if($order->jenisPembayaran==5)
                                <span class="label">JarvisPaymeny - Credit Card</span>
                            @endif
                            @if($order->jenisPembayaran==6)
                                <span class="label">JarvisPaymeny - Transfer Bank</span>
                            @endif
                        </td>
                        <td>
                            @if($order->status==0)
                            <span class="label label-warning">&lsaquo; Pending &rsaquo;</span>
                            @elseif($order->status==1)
                            <span class="label">&lsaquo; Konfirmasi masuk &rsaquo;</span>
                            @elseif($order->status==2)
                            <span class="label label-info">&lsaquo; Pembayaran diterima &rsaquo;</span>
                            @elseif($order->status==3)
                            <span class="label label-success">&lsaquo; Terkirim &rsaquo;</span>
                            @elseif($order->status==4)
                            <span class="label label-danger">&lsaquo; Batal &rsaquo;</span>
                            @endif
                        </td>
                        <td>
                            <a data-toggle="collapse" data-parent="#accordion2" href="#collapseOne" class="btn btn-mini btn-primary" type="button">Konfirmasi</a>
                            <button class="btn btn-mini btn-primary" type="button">Cancel</button>
                        </td>
                    </tr>
                </table>
                <br><br>
                <div id="collapseOne" class="accordion-body collapse out">

                  <div class="accordion-inner">
                    @if($order->jenisPembayaran==1)
                    <center><h2>Konfirmasi Pembayaran <small>transfer bank</small></h2></center>
                    <hr>
                    <div class="row-fluid">
                        <div class="span10 offset2">
                        {{Form::open(array('url'=> 'mycheckout/konfirmasi-order/'.$order->id, 'method'=>'put', 'class'=> 'form-horizontal'))}}   
                            <div class="control-group">
                                <label class="control-label" for="inputEmail" > Nama Pengirim</label>
                                <div class="controls">
                                    <input class="span6" type="text" name='nama' value='{{Input::old("nama")}}' required>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="inputEmail"> No Rekening</label>
                                <div class="controls">
                                    <input type="text" class="span6" name='noRekPengirim' value='{{Input::old("noRekPengirim")}}' required>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="inputEmail"> Rekening Tujuan</label>
                                <div class="controls" style="width: 40%;">
                                    <select name='bank' style="width: 100%;" required>
                                        <option value=''>-- Pilih Bank Tujuan --</option>
                                        @foreach ($bank_active as $bank)
                                            <option value="{{$bank->id}}">{{$bank->bankdefault->nama}} - {{$bank->noRekening}} - A/n {{$bank->atasNama}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="inputEmail" style=""> Jumlah</label>
                                <div class="controls">
                                    <input class="span6" type="text" name='jumlah' value='{{intval($order->total)}}' required>
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <div class="controls">
                                  <button type="submit" class="btn btn-info"><i class="icon-check icon-white"></i> Konfirmasi Order</button>
                                </div>
                            </div>
                        {{Form::close()}}
                        </div>
                    </div>
                    @endif
                    <!-- Paypal -->
                    @if($order->jenisPembayaran==2)
                        <center>
                        <p>Silakan melakukan pembayaran dengan paypal Anda secara online via paypal payment gateway. Transaksi ini berlaku jika pembayaran dilakukan sebelum 02 Jul 2013 pukul 17:26 WIB (2x24 jam). Klik tombol "Bayar Dengan Paypal" di bawah untuk melanjutkan proses pembayaran.</p>
                        {{$paypalbutton}}
                        </center>
                    @endif
                    @if($order->jenisPembayaran==4)
                        <center>
                        <p>Silakan melakukan pembayaran dengan iPaymu.Klik tombol "iPaymu" di bawah untuk melanjutkan proses pembayaran.</p>
                        <a class="btn btn-info" href="{{URL::to('ipaymu/'.$order->id)}}" target="_blank">Bayar dengan iPaymu</a>
                        </center>
                    @endif
                    @if($order->jenisPembayaran==6)
                        <center>
                        <h3>Pembayaran : Jarvis Payment - Bank Channel </h3>
                        <p>Silakan melakukan pembayaran melalui bank channel yang kami sediakan (ATM/I-banking). Pembayaran paling lambat dilakukan setelah 1x12 jam dari order ini dibuat.</p>
                        <h4>kode pembayaran: {{$jarvis_payment->payment_code}}</h4>
                        </center>
                    @endif
                  </div>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection

@section('content')
<div id="demos">
    <h1 class="text-center">Checkout - Data Pembeli dan Pengiriman</h1>
    <hr>
    <div id="psteps_horiz_layout" class="pf-form">
        <div class="row-fluid">
            <div >
                <a href="{{URL::to('mycheckout')}}" data-pjax><div class="step-title btn span3"><span class="step-order">1.</span> <span class="step-name hidden-phone">Rincian Belanja</span></div></a>
                <div class="step-title btn btn-success span3"><span class="step-order">2.</span> <span class="step-name">Data Pembeli</span></div>
                <div class="step-title btn disabled span3"><span class="step-order">3.</span> <span class="step-name hidden-phone">Metode Pembayaran</span></div>
                <div class="step-title btn disabled span3"><span class="step-order">4.</span> <span class="step-name hidden-phone">Ringkasan Order</span></div>
            </div>
        </div>
        <div class="row-fluid box">
            <div class="span12 box-content">
                <div class="step-content">
                    <form action="{{URL::to('mycheckout/pembayaran')}}" name='pengiriman' method='post' data-pjax>
                    <div class="row-fluid">
                        <div class="span6 well">
                            <div class="control-group">
                                <label class="control-label" for="inputEmail" > Nama</label>
                                <div class="controls">
                                  <input class="span10" type="text" name='nama' value='{{$user ? $user->nama : (Input::old("nama")? Input::old("nama") :($usertemp!=null?$usertemp["nama"]:''))}}' required>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="inputEmail"> Email</label>
                                <div class="controls">
                                  <input type="text" class="span10" id="email" name='email' value='{{$user ? $user->email :(Input::old("email")? Input::old("email") :($usertemp!=null?$usertemp["email"]:''))}}' required>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="inputEmail"> Alamat</label>
                                <div class="controls">
                                  <textarea class="span10" name='alamat' required>{{$user ? $user->alamat :(Input::old("alamat")? Input::old("alamat") :($usertemp!=null?$usertemp["alamat"]:''))}}</textarea>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="inputEmail"> Negara</label>
                                <div class="controls" >
                                    {{Form::select('negara',array('' => '-- Pilih Negara --') + $negara , ($user ? $user->negara :(Input::old("negara")? Input::old("negara") :($usertemp!=null?$usertemp["negara"]:''))), array('required'=>'', 'id'=>'negara'))}}
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="inputEmail"> Provinsi</label>
                                <div class="controls" id="provinsiPlace">
                                    {{Form::select('provinsi',array('' => '-- Pilih Provinsi --') + $provinsi , ($user ? $user->provinsi :(Input::old("provinsi")? Input::old("provinsi") :($usertemp!=null?$usertemp["provinsi"]:''))),array('required'=>'','id'=>'provinsi'))}}
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="inputEmail"> Kota</label>
                                <div class="controls" id="kotaPlace">
                                    {{Form::select('kota',array('' => '-- Pilih Kota --') + $kota , ($user ? $user->kota :(Input::old("kota")? Input::old("kota") :($usertemp!=null?$usertemp["kota"]:''))),array('required'=>'','id'=>'kota'))}}
                                </div>
                            </div>

                            <!--  -->
                            <div class="control-group">
                            <label class="control-label" for="inputEmail"> Kode Pos</label>
                            <div class="controls">
                              <input class="span6" type="text" name='kodepos' value='{{$user ? $user->kodepos :(Input::old("kodepos")? Input::old("kodepos") :($usertemp!=null?$usertemp["kodepos"]:''))}}' required>
                            </div>
                            </div>
                            <div class="control-group">
                            <label class="control-label" for="inputEmail"> Telepon / HP</label>
                            <div class="controls">
                              <input class="span10" type="text" name='telp' pattern="[0-9]+" onkeyup="cekInt(this)"value='{{$user ? $user->telp :(Input::old("telp")? Input::old("telp") :($usertemp!=null?$usertemp["telp"]:''))}}' placeholder="087xxxxxx" required>
                            </div>
                            </div>
                            <div class="control-group">
                            <label class="control-label" for="inputEmail"> Pesan</label>
                            <div class="controls">
                              <textarea class="span10" name="pesan">{{Input::old("pesan")}}{{($usertemp!=null?$usertemp["pesan"]:'')}}</textarea>
                            </div>
                            </div>

                        </div>
                        <div class="span6 well">
                            <div class="alert alert-block ">
                                <p>Kota tujuan pengiriman sesuai dengan yang telah dipilih dalam ekspedisi.</p>
                            </div>
                            <hr>
                            <h4>Data Penerima</h4>
                            <label class="radio">
                                <input type="radio" checked name='statuspenerima' value="0" {{($usertemp!=null?($usertemp["statuspenerima"]==0?'checked':''):'')}}> Data penerima sama dengan data pembeli                                
                            </label><br>
                            <label class="radio">
                                <input type="radio" name='statuspenerima' value="1" {{($usertemp!=null?($usertemp["statuspenerima"]=='1'?'checked':''):'')}}> Data penerima berbeda
                            </label>
                            <hr>
                            <div class="well" id='datapenerima'>
                                <div class="control-group">
                                    <label class="control-label" for="inputEmail">Nama Penerima</label>
                                    <div class="controls">
                                        <input class="span10" type="text" name='namapenerima' value="{{($usertemp!=null?$usertemp["namapenerima"]:'')}}">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="inputAlamat">Alamat</label>
                                    <div class="controls">
                                        <textarea class="span10" name='alamatpenerima'>{{($usertemp!=null?$usertemp["alamatpenerima"]:'')}}</textarea>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="inputTelepon">Telepon</label>
                                    <div class="controls">
                                        <input class="span10" type="text" name='telppenerima' pattern="[0-9]+" onkeyup="cekInt(this)"value='{{($usertemp!=null?$usertemp["telppenerima"]:'')}}'>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="inputKota">Kota</label>
                                    <div class="controls">
                                        <select disabled>
                                            <option value="{{$kotakirim}}">{{$kotakirim}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="inputKodepos">Kodepos</label>
                                    <div class="controls">
                                        <input type="text" id="inputKodepos" class="span10" name="kodepospenerima" value="{{($usertemp!=null?$usertemp["kodepospenerima"]:'')}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="clear:both;"></div>
                    <button class="next-button btn btn-info" type="submit">Lanjut <i class="icon-arrow-right"></i></button>
                    <a href="{{URL::to('mycheckout')}}" class="back-button btn btn-warning"><i class="icon-arrow-left"></i> Kembali</a
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    function cekInt(id){
        id.value = id.value.replace(/[^0-9]/g,'');
            if(id.value==''){
                id.value='';
            }
    }
</script>
@endsection
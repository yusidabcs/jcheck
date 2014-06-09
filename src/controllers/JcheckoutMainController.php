<?php namespace Bcscoder\Jcheckout;

class JcheckoutMainController extends JcheckoutBaseController {
	
	public function getIndex()
	{
		$pengaturan = $this->setting;
		if(\Shpcart::cart()->total()==0)
		{
			$this->layout->content = \View::make('jcheckout::general.step1')->with('cart' ,\Shpcart::cart());
		}else{

			//tipe toko umum
	        if ($pengaturan->checkoutType!=2) 
	        {
	            \Session::forget('pengiriman');

	            if(\URL::previous()!=\URL::to('mycheckout') && \URL::previous()!=\URL::to('pengiriman') && \URL::previous()!=\URL::to('pembayaran') && \URL::previous()!=\URL::to('konfirmasi')){                
	                \Session::forget('besarPotongan');
	                \Session::forget('diskonId');
	                \Session::forget('tipe'); 
	                \Session::forget('tujuan');
	                \Session::forget('ekspedisiId');
	                \Session::forget('ongkosKirim');
	            }
	            $kode = rand(100,200);
	            if($pengaturan->statusEkspedisi!=1){
	                if($pengaturan->statusEkspedisi==2)
	                    \Session::set('ekspedisiId',"Free Shipping");
	                if($pengaturan->statusEkspedisi==3)
	                    \Session::set('ekspedisiId',"Pengiriman Menyusul");
	                \Session::set('ongkosKirim',0);
	            }
	            //$eks = New ShopCartController;
	            //$data = $eks->checkekspedisi('surabaya');
	            $selected = \Session::get('ekspedisiId').';'.\Session::get('ongkosKirim');

	            if(\Session::has('ekspedisiId')){
	                $status =1;
	                $ekspedisi = array('tujuan'=>\Session::get('tujuan'),'ekspedisi'=>\Session::get('ekspedisiId'),'tarif'=>\Session::get('ongkosKirim'));
	            }else{
	                $status =0;
	                $ekspedisi=null;
	            }
	            if(\Session::has('diskonId')){            
	                $diskon = array('diskonId' => \Diskon::find(\Session::get('diskonId')), 'besarPotongan'=>\Session::get('besarPotongan'));
	            }else{
	                $diskon=null;
	            }
	            \Session::put('kodeunik',$kode);
	            
	            if ($pengaturan->checkoutType==3) 
	            {
	                if (!\Shpcart::cart()->contents()) 
	                {
	                    return \Redirect::to('');
	                }

	                $cart_contents = \Shpcart::cart()->contents();

	                //return $cart_contents;
	                foreach ($cart_contents as $key => $value) 
	                {
	                    $idproduk = $value['produkId'];
	                }
	                $this->layout->content = \View::make('jcheckout::po.postep1')->with('cart' ,\Shpcart::cart())
	                    ->with('provinsi' ,\Provinsi::where('negaraId','=',$this->setting->negara)->get())
	                    ->with('kodeunik',$kode)
	                    ->with('pengaturan' ,$pengaturan)
	                    ->with('statusEkspedisi',$status)
	                    ->with('ekspedisi',$ekspedisi)
	                    ->with('kontak', $this->setting)
	                    ->with('akun',\Akun::find($this->akunId))
	                    ->with('dp', \PreorderProduk::where('produkId', $idproduk)->where('status', '0')->first()->dp)
	                    ->with('pajak',\Pajak::where('akunId','=',$this->akunId)->first());
	            }
	            elseif ($pengaturan->checkoutType==1) 
	            {
	                $this->layout->content = \View::make('jcheckout::general.step1')->with('cart' ,\Shpcart::cart())
	                    ->with('provinsi' ,\Provinsi::where('negaraId','=',$this->setting->negara)->get())
	                    ->with('kodeunik',$kode)
	                    ->with('pengaturan' ,$pengaturan)
	                    ->with('statusEkspedisi',$status)
	                    ->with('ekspedisi',$ekspedisi)
	                    ->with('diskon',$diskon)
	                    ->with('kontak', $this->setting)
	                    ->with('akun',\Akun::find($this->akunId))
	                    ->with('pajak',\Pajak::where('akunId','=',$this->akunId)->first());
	            }
	            
	            $this->layout->seo = \View::make('jcheckout::seostuff')
	            ->with('title',"Checkout - Rincian Belanja - ".$this->setting->nama)
	            ->with('description',$this->setting->deskripsi)
	            ->with('keywords',$this->setting->keyword);
	        }
	        else
	        {
	            $this->layout->content = \View::make('jcheckout::inquiry.inquiry1')->with('cart' ,\Shpcart::wishlist())
	                ->with('provinsi' ,\Provinsi::where('negaraId','=',$this->setting->negara)->get())
	                ->with('pengaturan' ,$pengaturan)
	                ->with('kontak', $this->setting)
	                ->with('akun',\Akun::find($this->akunId)
	                );
	            $this->layout->seo = View::make('jcheckout::seostuff')
	            ->with('title',"Checkout - Rincian Belanja - ".$this->setting->nama)
	            ->with('description',$this->setting->deskripsi)
	            ->with('keywords',$this->setting->keyword);
	        }
		}
	}

	public function getPengiriman()
    {
        //check session cart dan ekspedisi dan diskon                
        if ($this->setting->checkoutType!=2) 
        {
            if(\Shpcart::cart()->total_items()==0 || !(\Session::has('ekspedisiId'))) 
            {
                return \Redirect::to('mycheckout');
            }
        }
        else
        {
            if(Shpcart::wishlist()->total_items()==0) 
            {
                return \Redirect::to('mycheckout');
            }
        }

       //check ekspedisi
        if ($this->setting->checkoutType!=2) 
        {
            if($this->setting->statusEkspedisi==1){
                if(!\Session::has('ekspedisiId')){
                     return \Redirect::to('mycheckout');
                }
            }
        }
        
        if(\Session::has('message')){            
            echo "<div class='".S\ession::get('message')."' id='message' style='display:none'>
                <p>".\Session::get('text')."</p>
            </div>";
        }       
        $negara = \Negara::remember(30)->lists('nama','id');
        $provinsi = \Provinsi::remember(30)->lists('nama','id');
        $kota = \Kabupaten::remember(30)->lists('nama','id'); 
        if ($this->setting->checkoutType==1) 
        {
            $this->layout->content = \View::make('jcheckout::general.step2')->with('cart' ,\Shpcart::cart())
                ->with('provinsi' ,\Provinsi::where('negaraId','=',$this->setting->negara)->get())
                ->with('user',(\Sentry::check() ? (\Session::has('pengiriman') ? null:\Sentry::getUser()):null))
                ->with('negara', $negara)
                ->with('provinsi',$provinsi)
                ->with('kotakirim', \Session::get('tujuan'))
                ->with('kota', $kota)
                ->with('usertemp',(\Session::has('pengiriman')?\Session::get('pengiriman'):null))
                ->with('kontak', $this->setting);
            $this->layout->seo = \View::make('jcheckout::seostuff')
            ->with('title',"Checkout - Data Pembeli dan Pengiriman - ".$this->setting->nama)
            ->with('description',$this->setting->deskripsi)
            ->with('keywords',$this->setting->keyword);
        }
        elseif ($this->setting->checkoutType==3) 
        {
            $this->layout->content = \View::make('jcheckout::po.postep2')->with('cart' ,\Shpcart::cart())
                ->with('provinsi' ,\Provinsi::where('negaraId','=',$this->setting->negara)->get())
                ->with('user',(\Sentry::check() ? (\Session::has('pengiriman') ? null:\Sentry::getUser()):null))
                ->with('negara', $negara)
                ->with('provinsi',$provinsi)
                ->with('kotakirim', \Session::get('tujuan'))
                ->with('kota', $kota)
                ->with('usertemp',(\Session::has('pengiriman')?\Session::get('pengiriman'):null))
                ->with('kontak', $this->setting);
            $this->layout->seo = \View::make('jcheckout::seostuff')
            ->with('title',"Checkout - Data Pembeli dan Pengiriman - ".$this->setting->nama)
            ->with('description',$this->setting->deskripsi)
            ->with('keywords',$this->setting->keyword);
        }
        elseif ($this->setting->checkoutType==2)  
        {
            $this->layout->content = \View::make('jcheckout::inquiry.inquiry2')->with('cart' ,\Shpcart::wishlist())
                ->with('provinsi' ,\Provinsi::where('negaraId','=',$this->setting->negara)->get())
                ->with('user',(\Sentry::check() ? (\Session::has('pengiriman') ? null:\Sentry::getUser()):null))
                ->with('negara', $negara)
                ->with('provinsi',$provinsi)
                ->with('kota', $kota)
                ->with('usertemp',(\Session::has('pengiriman')?\Session::get('pengiriman'):null))
                ->with('kontak', $this->setting);
            $this->layout->seo = \View::make('jcheckout::seostuff')
            ->with('title',"Checkout - Data Pembeli dan Pengiriman - ".$this->setting->nama)
            ->with('description',$this->setting->deskripsi)
            ->with('keywords',$this->setting->keyword);
        }
    }


    public function getPembayaran(){
        if ( ! \Sentry::check()){
            
            $user = \Pelanggan::where('email','=',\Input::get('email'))->whereIn('tipe', array(1,2))->where('akunId','=',$this->akunId)->get();
            if($user->count()>0){
                return \Redirect::to('mycheckout/pengiriman')->withInput()->with('message','error')->with('text','Alamat email sudah digunakan. Coba yang lain atau silakan login.');
            }            
        }
        if(\Request::server('REQUEST_METHOD')=='POST'){
            \Session::put('pengiriman', \Input::all());               
        }        
        $akun = \OnlineAkun::where('akunId','=',$this->akunId)->get();      
        if ($this->setting->checkoutType==1) 
        {
            if (@$akun[2]) 
            {
                $this->layout->content = \View::make('jcheckout::general.step3')->with('cart' ,\Shpcart::cart())
                    ->with('banks',\BankDefault::all())
                    ->with('user',(\Sentry::check() ? \Sentry::getUser():''))
                    ->with('banktrans' ,\Bank::where('akunId','=',$this->akunId)->where('status','=',1)->get())
                    ->with('paypal' , $akun[0])
                    ->with('creditcard', $akun[1])
                    ->with('ipaymu', $akun[2])
                    ->with('pembayaran',\Session::has('pembayaran')? \Session::get('pembayaran'):null)
                    ->with('kontak', $this->setting);
            }
            else
            {
                $this->layout->content = \View::make('jcheckout::general.step3')->with('cart' ,\Shpcart::cart())
                    ->with('banks',\BankDefault::all())
                    ->with('user',(\Sentry::check() ? \Sentry::getUser():''))
                    ->with('banktrans' ,\Bank::where('akunId','=',$this->akunId)->where('status','=',1)->get())
                    ->with('paypal' , $akun[0])
                    ->with('creditcard', $akun[1])
                    ->with('pembayaran',\Session::has('pembayaran')? \Session::get('pembayaran'):null)
                    ->with('kontak', $this->setting);
            } 
            
            $this->layout->seo = \View::make('jcheckout::seostuff')
                ->with('title',"Checkout - Metode Pembayaran - ".$this->setting->nama)
                ->with('description',$this->setting->deskripsi)
                ->with('keywords',$this->setting->keyword);
        }
        elseif ($this->setting->checkoutType==3) 
        {
            $this->layout->content = \View::make('jcheckout::po.postep3')->with('cart' ,\Shpcart::cart())
                ->with('banks',\BankDefault::all())
                ->with('user',(\Sentry::check() ? \Sentry::getUser():''))
                ->with('banktrans' ,\Bank::where('akunId','=',$this->akunId)->where('status','=',1)->get())
                ->with('paypal' , $akun[0])
                ->with('creditcard', $akun[1])
                ->with('ipaymu', @$akun[2])
                ->with('pembayaran',\Session::has('pembayaran')? \Session::get('pembayaran'):null)
                ->with('kontak', $this->setting);
            $this->layout->seo = \View::make('jcheckout::seostuff')
                ->with('title',"Checkout - Metode Pembayaran - ".$this->setting->nama)
                ->with('description',$this->setting->deskripsi)
                ->with('keywords',$this->setting->keyword);
        }
        
    }


    public function getKonfirmasi()
    {
        if ($this->setting->checkoutType!=2) 
        {
            if(\Request::server('REQUEST_METHOD')=='POST'){
                \Session::put('pembayaran',\Input::all());
                $pembayaran = \Input::all();
            }
            if(\Session::has('pembayaran')){
                $pembayaran =\Session::get('pembayaran');
            } 
            $cart_contents = \Shpcart::cart()->contents();
        }
        if ($this->setting->checkoutType==2) 
        {
            if(\Request::server('REQUEST_METHOD')=='POST'){
                \Session::put('pengiriman', \Input::all());               
            }  
        }
        
        $tujuanekspedisi = \Kabupaten::where('nama', 'LIKE', "%".\Session::get('tujuan')."%")->first();

        $datapengirim = \Session::get('pengiriman');
        $datapengirim['negara'] = \Negara::find($datapengirim['negara'])->nama;
        $datapengirim['provinsi'] = \Provinsi::find($datapengirim['provinsi'])->nama;
        $datapengirim['kota'] = \Kabupaten::find($datapengirim['kota'])->nama;

        if ($this->setting->checkoutType!=2) 
        {
            $datapengirim['kotapenerima'] = \Session::get('tujuan');    
        }

        $akun = \OnlineAkun::where('akunId','=',$this->akunId)->get();
        $pajak = \Pajak::where('akunId','=',$this->akunId)->first();
        $potongan = 0;
        
        if ($this->setting->checkoutType!=2) 
        {
            if(!is_null(\Session::get('diskonId'))){
                if(\Session::get('tipe')==1){
                    $potongan = \Session::get('besarPotongan');                
                }else{
                    $potongan = (\Shpcart::cart()->total()*\Session::get('besarPotongan')/100);
                }
            }

            $total = (\Shpcart::cart()->total() + \Session::get('ongkosKirim')- $potongan);        
            $total = $total + ($pajak->status==0 ? 0 : $total * \Pajak::where('akunId','=',$this->akunId)->first()->pajak / 100) + \Session::get('kodeunik');        
            
            if ($this->setting->checkoutType==1) 
            {
                 $this->layout->content = \View::make('jcheckout::general.step4')->with('cart' ,\Shpcart::cart())
                    ->with('datapengirim',$datapengirim)
                    ->with('dataekspedisi',\Session::get('ekspedisiId'))
                    ->with('kotakirim', \Session::get('tujuan'))
                    ->with('datapembayaran',$pembayaran)
                    ->with('kodekupon' ,\Session::has('diskonId') ? \Diskon::find(\Session::get('diskonId'))->kode : '')
                    ->with('kodeunik', \Session::get('kodeunik'))
                    ->with('diskon', $potongan)
                    ->with('total', $total)
                    ->with('kontak', $this->setting)
                    ->with('pajak',$pajak);
                $this->layout->seo = \View::make('checkout::seostuff')
                    ->with('title',"Checkout - Ringkasan Order - ".$this->setting->nama)
                    ->with('description',$this->setting->deskripsi)
                    ->with('keywords',$this->setting->keyword);
            }
            elseif ($this->setting->checkoutType==3) 
            {
                foreach ($cart_contents as $key => $value) 
                {
                    $preorderdata   = \PreorderProduk::where("produkId", $value['produkId'])->where("status", "0")->first();
                }
                
                $dp= $preorderdata->dp;
                $totaldp = ($preorderdata->dp*\Shpcart::cart()->total_items() + \Session::get('ongkosKirim')- $potongan) + \Session::get('kodeunik');  
                
                $this->layout->content = \View::make('jcheckout::po.postep4')->with('cart' ,\Shpcart::cart())
                    ->with('datapengirim',$datapengirim)
                    ->with('dataekspedisi',\Session::get('ekspedisiId'))
                    ->with('kotakirim', \Session::get('tujuan'))
                    ->with('datapembayaran',$pembayaran)
                    ->with('kodekupon' ,\Session::has('diskonId') ? \Diskon::find(\Session::get('diskonId'))->kode : '')
                    ->with('kodeunik', \Session::get('kodeunik'))
                    ->with('total', $total)
                    ->with('dp', $dp)
                    ->with('totaldp', $totaldp)
                    ->with('kontak', $this->setting)
                    ->with('pajak',$pajak);
                $this->layout->seo = \View::make('jcheckout::seostuff')
                    ->with('title',"Checkout - Ringkasan Order - ".$this->setting->nama)
                    ->with('description',$this->setting->deskripsi)
                    ->with('keywords',$this->setting->keyword);
            }

           
        }
        else
        {
            $this->layout->content = \View::make('checkout::inquiry.inquiry3')->with('cart' ,\Shpcart::wishlist())
                ->with('datapengirim',$datapengirim)
                ->with('kontak', $this->setting
            );
            $this->layout->seo = \View::make('checkout::seostuff')
                ->with('title',"Checkout - Ringkasan Order - ".$this->setting->nama)
                ->with('description',$this->setting->deskripsi)
                ->with('keywords',$this->setting->keyword);
        }
    }
    public function getFinish()
    {
        if ($this->setting->checkoutType!=2) 
        {
            if(\Shpcart::cart()->total()==0){
                return \Redirect::to('mycheckout');
            }    
        }
        else
        {
            if(\Shpcart::wishlist()->total_items()==0){
                return \Redirect::to('mycheckout');
            }  
        }
        
        //Generate kd Order
        $pengaturan = $this->setting;        
        $awal = date('ymd');
        $next_id ='';   
        if ($pengaturan->checkoutType==1) 
        {
        	$order = \Order::orderBy('created_at', 'desc')->first();
            if(!is_null($order)){
                $next_id = $order->kodeOrder;      
            }
        }   
        elseif ($pengaturan->checkoutType==3) 
        {
        	$order = \Preorder::orderBy('created_at', 'desc')->first();
            if(!is_null($order)){            
                $next_id = $order->kodePreorder;      
            }
        } 
        elseif ($pengaturan->checkoutType==2) 
        {
        	$order = \Inquiry::orderBy('created_at', 'desc')->first();
            if(!is_null($order)){            
                $next_id = $order->kodeInquiry;      
            }   
        }
        
        if($next_id!=''){
            $next_id = substr($next_id,6,10);
            $next_id ++;
        }else{
            $next_id= 1;
        }
        $nol = (str_repeat('0',(4-strlen($next_id))));
        $kdOrder = $awal.$nol.$next_id;
        //end generate kode order

        $datapengirim = \Session::get('pengiriman');
        if ($pengaturan->checkoutType!=2) 
        {
            $pembayaran = \Session::get('pembayaran');
        }
        
        //cek guest atau pelanggan
        if ( ! \Sentry::check()){
            //guest            
            $datapengirim['kotanama'] = \Kabupaten::find($datapengirim['kota'])->nama;

            //$user = new Pelanggan;
            $data = array(
                'nama' => $datapengirim['nama'],
                'email'    => $datapengirim['email'],
                'password' => 'guest',
                'kodepos' => $datapengirim['kodepos'],
                'perusahaan' => '',
                'telp' => $datapengirim['telp'],
                'alamat' => $datapengirim['alamat'],
                'negara' => $datapengirim['negara'],
                'provinsi' => $datapengirim['provinsi'],
                'kota' => $datapengirim['kota'],
                'tglLahir' => '',
                'catatan' => '',
                'tags' => '',
                'tipe' => 0,
                'tanggalMasuk' => date("Y-m-d"),
                'activated' => 1,
                'akunId' => $this->akunId
            );
           $pelangganId =  \DB::table('pelanggan')->insertGetId(
                    array(
                    'nama' => $datapengirim['nama'],
                    'email'    => $datapengirim['email'],
                    'password' => 'guest',
                    'kodepos' => $datapengirim['kodepos'],
                    'perusahaan' => '',
                    'telp' => $datapengirim['telp'],
                    'alamat' => $datapengirim['alamat'],
                    'negara' => $datapengirim['negara'],
                    'provinsi' => $datapengirim['provinsi'],
                    'kota' => $datapengirim['kota'],
                    'tglLahir' => '',
                    'catatan' => '',
                    'tags' => '',
                    'tipe' => 0,
                    'tanggalMasuk' => date("Y-m-d"),
                    'activated' => 1,
                    'akunId' => $this->akunId
                )  
            );
        }else{
            //pelanggan
            $pelangganId = Sentry::getUser()->id;
        }
        //ekspedisi 

        //save order
        if ($pengaturan->checkoutType==1) 
        {
            $ekspedisi =\Session::get('ekspedisiId');
            $jenispengiriman = \Session::get('ekspedisiId');
            $ongkosKirim = \Session::get('ongkosKirim');
            $potongan = 0;
            $pajak = \Pajak::where('akunId','=',$this->akunId)->first();
            //get total order
            if(!is_null(\Session::get('diskonId'))){
                if(\Session::get('tipe')==1){
                    $potongan = \Session::get('besarPotongan');                
                }else{
                    $potongan = (\Shpcart::cart()->total()*\Session::get('besarPotongan')/100);
                }
            }
            $total = (\Shpcart::cart()->total() + \Session::get('ongkosKirim')- $potongan) + \Session::get('kodeunik');        
            $total = $total + ($pajak->status==0 ? 0 : $total * $pajak->pajak / 100);
            
            $order = new \Order;
            $order->kodeOrder = $kdOrder;
            $order->tanggalOrder = date('Y-m-d H:m:s');
            $order->pelangganId = $pelangganId;
            $order->total= $total;
            $order->status= 0;
            $order->jenisPengiriman = $jenispengiriman;
            $order->ongkoskirim = \Session::get('ongkosKirim');
            if($datapengirim['statuspenerima']==0)
            {
                $order->nama = $datapengirim['nama'];
                $order->telp = $datapengirim['telp'];
                $order->alamat = $datapengirim['alamat'];
                $order->kota = \Session::get('tujuan');    
            }
            else
            {
                $order->nama = $datapengirim['namapenerima'];
                $order->telp = $datapengirim['telppenerima'];
                $order->alamat = $datapengirim['alamatpenerima'];
                $order->kota = \Session::get('tujuan');
            }
            if($pengaturan->statusEkspedisi>1){
                $order->kota = $datapengirim['kota'];
            }
            $order->pesan = $datapengirim['pesan'];
            $order->noResi = '';
            $order->ekspedisiId = '';
            if(\Session::get('pembayaran')['tipepembayaran']=='bank'){
                $pembayaran =1;
            }else if(\Session::get('pembayaran')['tipepembayaran']=='paypal'){
                $pembayaran =2;           
            }else if(\Session::get('pembayaran')['tipepembayaran']=='creditcard'){
                $pembayaran =3;
            }else if(\Session::get('pembayaran')['tipepembayaran']=='ipaymu'){
                $pembayaran =4;
            }
            else if(\Session::get('pembayaran')['tipepembayaran']=='jarvis_payment'){
            	if(\Session::get('pembayaran')['jarvis_payment_type']=='credit_card')
            	{
            		$pembayaran =5;	
            	}
            	else if(\Session::get('pembayaran')['jarvis_payment_type']=='bank_channel')
            	{
            		$pembayaran =6;	
            	}
            }    
            
            $order->jenisPembayaran = $pembayaran;
            $order->diskonId = \Session::has('diskonId') ? \Session::get('diskonId') : '';
            $order->akunId = $this->akunId;
            $order->save();

            if($order)
            {
                //cek diskon
                if(\Session::has('diskonId'))
                {
                    $diskonId = \Session::get('diskonId');   
                    $diskon = \Diskon::find($diskonId);
                    $diskon->klaim = $diskon->klaim +1;
                    $diskon->save();
                }
                else
                {
                    $diskonId='';
                }

                //tambah det order
                $cart_contents = \Shpcart::cart()->contents();
                foreach ($cart_contents as $key => $value) {
                    $detorder = new \DetailOrder;
                    $detorder->orderId=$order->id;
                    $detorder->opsiSkuId= is_null($value['opsiskuId']) ? '':$value['opsiskuId'];
                    $detorder->produkId = $value['produkId'];
                    $detorder->qty = $value['qty'];
                    $detorder->hargaSatuan = $value['price'];
                    $detorder->created_at = date('Y-m-d H:m:s');
                    $detorder->updated_at = date('Y-m-d H:m:s');
                    $detorder->save();
                }
                $bank_default = \BankDefault::all();
                $bank_active = \Bank::where('akunId','=',$this->akunId)->where('status','=',1)->get();
                //generate info cart for email
                 $cart ='<table id="items" style="margin: 30px 0 0 0;padding: 0;border-collapse: collapse;clear: both;width: 100%;border: 1px solid black;"><tr style="margin: 0;padding: 0;">
                                <th style="margin: 0;padding: 5px;border: 1px solid black;background: #eee;">No</th>
                                <th style="margin: 0;padding: 5px;border: 1px solid black;background: #eee;">Nama Produk</th>
                                <th style="margin: 0;padding: 5px;border: 1px solid black;background: #eee;">Varian</th>
                                <th style="margin: 0;padding: 5px;border: 1px solid black;background: #eee;">Qty</th>
                                <th style="margin: 0;padding: 5px;border: 1px solid black;background: #eee;">Harga</th>          
                                <th style="margin: 0;padding: 5px;border: 1px solid black;background: #eee;">Subtotal</th>
                            </tr>';    
                $cart = $cart.\View::make('admin.order.listcart')->with('cart_contents', \Shpcart::cart()->contents())->with('berat','0')->with('jenisongkircart',$jenispengiriman)->with('ongkircart',$order->ongkoskirim)->with('totalordercart',$order->total);
                $cart = $cart."</table>";

                $bank = \View::make('admin.pengaturan.bank')->with('banks', $bank_default) ->with('banktrans', $bank_active);
                \Shpcart::cart()->destroy();
                //kirim email order ke pelanggan
                //$template = Templateemail::find(1);
                $template = \Templateemail::where('akunId','=',$this->akunId)->where('no','=',1)->first();
                $data = array(
                    'pelanggan'=> $order->nama,
                    'pelangganalamat'=> $order->alamat,
                    'pelangganphone'=> $order->telp,
                    'toko' => $this->setting->nama,
                    'kodeorder' => $order->kodeOrder,
                    'tanggal' => $order->tanggalOrder,
                    'cart' => $cart,
                    'rekeningbank' =>$bank,
                    'ekspedisi' =>$order->jenisPengiriman,
                    'totalbelanja' =>$order->total,
                    'phone' => $this->setting->telepon,
                    'handphone' => $this->setting->hp,
                    'email' => $this->setting->email
                    );

                $datapengirim['fromemail']= $this->setting->email;
                $datapengirim['fromtoko']= $this->setting->nama;
                $email = bind_to_template($data,$template->isi);    
     
                //kirim email ke pelanggan
                $subject = bind_to_template($data,$template->judul);  
                \Mail::later(3,'emails.email',array('data'=>$email), function($message) use ($subject,$datapengirim)
                {   
                    $message->from($datapengirim['fromemail'],$datapengirim['fromtoko']);
                    $message->to($datapengirim['email'], $datapengirim['nama'])->subject($subject);
                });

                //kirim email konfirmasi ke email toko
                $pengaturan['pengirim']=$this->setting->email;
                $subject2 = 'Pemberitahuan Order -- '.bind_to_template($data,$template->judul);  
                \Mail::later(5,'emails.email',array('data'=>$email), function($message) use ($subject2,$pengaturan)
                {   
                    //$message->from($pengaturan->pengirim);
                    $message->to($pengaturan->emailAdmin, $pengaturan->nama)->subject($subject2);
                });


                $akun = \OnlineAkun::where('akunId','=',$this->akunId)->get();
                $paypalbutton = "";
                if($order->jenisPembayaran==2){
                    //buat button paypal.
                    $paypal = new \GoPayPal(THIRD_PARTY_CART);
                    $paypal->sandbox = false;
                    $paypal->openInNewWindow = true;
                    $paypal->set('business', $akun[0]->acount);
                    $paypal->set('currency_code', 'USD');
                    $paypal->set('country', 'US');
                    $paypal->set('return', \URL::to('konfirmasiorder/'.$order->id));
                    $paypal->set('cancel_return', \URL::to('konfirmasiorder/'.$order->id));
                    $paypal->set('notify_url', \URL::to('konfirmasiorder/'.$order->id)); # rm must be 2, need to be hosted online
                    $paypal->set('rm', 2); # return by POST
                    $paypal->set('no_note', 0);
                    $paypal->set('custom', md5(time()));
                    $paypal->set('cbt', 'Return to our site to validate your payment!'); # caption override for "Return to Merchant" button                
                    $paypal->set('handling_cart', 1); # this overide the individual items' handling "handling_x"
                    $paypal->set('tax_cart', $akun[0]->fee);  
                    $item = new \GoPayPalCartItem();
                    $item->set('item_name', 'Payment for order : #'.$order->kodeOrder);
                    $item->set('item_number', '1');
                    $total = $order->total;
                    if($this->setting->mataUang == 1){
                        $total =round($order->total / \OnlineAkun::where('akunId','=',$this->akunId)->first()->rate); 
                    }
                    $item->set('amount', $total);
                    $item->set('quantity', 1);
                    $item->set('shipping', 0.1);
                    $item->set('handling', 1); # this is overriden by "handling_cart"
                    $paypal->addItem($item);
                    # If you set your custom button here, PayPal Pay Now button will be displayed.
                    $paypal->setButton('<button type="submit">Bayar Dengan Paypal - The safer, easier way to pay online!</button>');
                    $paypalbutton=$paypal->html();      
                }

                \Shpcart::cart()->destroy();
                \Session::forget('diskonId');
                \Session::forget('besarPotongan');
                \Session::forget('tipe');
                \Session::forget('pengiriman');
                \Session::forget('pembayaran');
                \Session::forget('ekspedisiId');
                \Session::forget('ongkosKirim');
                \Session::forget('kodeunik');            
                
                if (@$akun[2]) 
                {
                    $this->layout->content = \View::make('jcheckout::general.step5')->with('datapengirim' ,$datapengirim)
                    ->with('datapembayaran', $pembayaran)
                    ->with('order', $order)
                    ->with('banks' ,$bank_default)
                    ->with('banktrans', $bank_active)
                    ->with('paypal',  $akun[0])
                    ->with('creditcard' , $akun[1])
                    ->with('ipaymu' , $akun[2])
                    ->with('pengaturan', $this->setting)
                    ->with('paypalbutton', $paypalbutton)
                    ->with('kontak', $this->setting);
                }
                else
                {
                    $this->layout->content = \View::make('jcheckout::general.step5')->with('datapengirim' ,$datapengirim)
                    ->with('datapembayaran', $pembayaran)
                    ->with('order', $order)
                    ->with('banks' ,$bank_default)
                    ->with('banktrans', $bank_active)
                    ->with('paypal',  $akun[0])
                    ->with('creditcard' , $akun[1])
                    ->with('pengaturan', $this->setting)
                    ->with('paypalbutton', $paypalbutton)
                    ->with('kontak', $this->setting);
                }
                //sendGcm(0, $order->id);

                $this->layout->seo = \View::make('jcheckout::seostuff')
                ->with('title',"Checkout - Finish - ".$this->setting->nama)
                ->with('description','$this->setting->deskripsi')
                ->with('keywords','$this->setting->keyword');
            }

        }
        elseif ($pengaturan->checkoutType==3) 
        {
            $ekspedisi =\Session::get('ekspedisiId');
            $jenispengiriman = \Session::get('ekspedisiId');
            $ongkosKirim = \Session::get('ongkosKirim');
            $potongan = 0;
            $pajak = \Pajak::where('akunId','=',$this->akunId)->first();
            
            //get total order
            if(!is_null(\Session::get('diskonId'))){
                if(\Session::get('tipe')==1){
                    $potongan = \Session::get('besarPotongan');                
                }else{
                    $potongan = (\Shpcart::cart()->total()*\Session::get('besarPotongan')/100);
                }
            }
            $total = (\Shpcart::cart()->total() + \Session::get('ongkosKirim')- $potongan) + \Session::get('kodeunik');        
            $total = $total + ($pajak->status==0 ? 0 : $total * $pajak->pajak / 100);
            
            $preorder = new \Preorder;
            $preorder->kodePreorder = $kdOrder;
            $preorder->tanggalPreorder = date('Y-m-d H:m:s');
            $preorder->pelangganId = $pelangganId;
            $preorder->total= $total;
            $preorder->status= 0;
            $preorder->jenisPengiriman = $jenispengiriman;
            $preorder->ongkoskirim = \Session::get('ongkosKirim');
            if($datapengirim['statuspenerima']==0)
            {
                $preorder->nama = $datapengirim['nama'];
                $preorder->telp = $datapengirim['telp'];
                $preorder->alamat = $datapengirim['alamat'];
                $preorder->kota = \Kabupaten::find($datapengirim['kota'])->nama;    
            }
            else
            {
                $preorder->nama = $datapengirim['namapenerima'];
                $preorder->telp = $datapengirim['telppenerima'];
                $preorder->alamat = $datapengirim['alamatpenerima'];
                $preorder->kota = \Session::get('tujuan');
            }
            $preorder->pesan = $datapengirim['pesan'];
            $preorder->noResi = '';
            $preorder->ekspedisiId = '';
            if(\Session::get('pembayaran')['tipepembayaran']=='bank'){
                $pembayaran =1;
            }else if(\Session::get('pembayaran')['tipepembayaran']=='paypal'){
                $pembayaran =2;           
            }else if(\Session::get('pembayaran')['tipepembayaran']=='creditcard'){
                $pembayaran =3;
            }
            else if(\Session::get('pembayaran')['tipepembayaran']=='jarvis_payment'){
            	if(\Session::get('pembayaran')['jarvis_payment_type']=='credit_card')
            	{
            		$pembayaran =5;	
            	}
            	else if(\Session::get('pembayaran')['jarvis_payment_type']=='bank_channel')
            	{
            		$pembayaran =6;	
            	}
            }

            $preorder->jenisPembayaran = $pembayaran;
            $cart_contents = \Shpcart::cart()->contents();
            foreach ($cart_contents as $key => $value) 
            {
                $preorderdata   = \PreorderProduk::where("produkId", $value['produkId'])->where("status", "0")->first();
                $preorder->preorderprodukId = $preorderdata->id;
                $preorder->jumlah   = $value['qty'];
                $preorder->opsiSkuId= is_null($value['opsiskuId']) ? '':$value['opsiskuId'];
                $preorder->hargaSatuan = $value['price'];
            }
            $totaldp = ($preorderdata->dp *\Shpcart::cart()->total_items()+ \Session::get('ongkosKirim')- $potongan) + \Session::get('kodeunik');
            
            $preorder->dp = $totaldp;
            $preorder->akunId = $this->akunId;
            $preorder->save();

            if($preorder)
            {
            	$bank_default = \BankDefault::all();
                $bank_active = \Bank::where('akunId','=',$this->akunId)->where('status','=',1)->get();
                //kirim email konfirmasi ke email user
                 $cart ='<table border="1" cellpadding="5"><tr>
                                <td>No</td>
                                <td>Nama Produk</td>
                                <td>Varian</td>
                                <td>Qty/Harga</td>        
                                <td>Subtotal</td>
                            </tr>';            
                $cart = $cart.\View::make('admin.order.listcart')->with('cart_contents', \Shpcart::cart()->contents())->With('berat','0')->with('cart_contents', Shpcart::cart()->contents())->with('berat','0')->with('jenisongkircart',$jenispengiriman)->with('ongkircart',$preorder->ongkoskirim)->with('totalordercart',$preorder->total);
                $cart = $cart."<tr>     
                                <td colspan=4>
                                    <h3 class='pull-right'>Total Orderan</h3>
                                </td>
                                <td colspan=2><h4>".jadiRupiah($preorder->total)."</h4></td>
                            </tr></table>";
                $cart = $cart."
                                <tr>
                                <td colspan='4'><h3 class='pull-right'>Total pembayaran awal (DP)</h3></td>
                                <td colspan='2'><h4>".jadiRupiah($preorder->dp)."</h4></td>
                                </tr>
                                <table>";
                $bank = \View::make('admin.pengaturan.bank')->with('banks', $bank_default) ->with('banktrans', $bank_active);
                \Shpcart::cart()->destroy();
                //kirim email order ke pelanggan
                //$template = Templateemail::find(1);
                $template = \Templateemail::where('akunId','=',$this->akunId)->where('no','=',1)->first();
                $data = array(
                    'pelanggan'=> $preorder->nama,
                    'pelangganalamat'=> $preorder->alamat,
                    'pelangganphone'=> $preorder->telp,
                    'toko' => $this->setting->nama,
                    'kodeorder' => $preorder->kodePreorder,
                    'tanggal' => $preorder->tanggalPreorder,
                    'cart' => $cart,
                    'rekeningbank' =>$bank,
                    'ekspedisi' =>$preorder->jenisPengiriman,
                    'totalbelanja' =>$preorder->total
                    );
                $datapengirim['fromemail']= $this->setting->email;
                $datapengirim['fromtoko']= $this->setting->nama;
                $email = bind_to_template($data,$template->isi);            
                $subject = bind_to_template($data,$template->judul);  
                \Mail::later(2,'emails.email',array('data'=>$email), function($message) use ($subject,$datapengirim)
                {   
                    $message->from($datapengirim['fromemail'],$datapengirim['fromtoko']);
                    $message->to($datapengirim['email'], $datapengirim['nama'])->subject($subject);
                });
                //kirik email konfirmasi ke email toko
                $subject2 = 'Pemberitahuan Order -- '.bind_to_template($data,$template->judul);  
                \Mail::later(3,'emails.email',array('data'=>$email), function($message) use ($subject2,$pengaturan)
                {   
                    $message->to($pengaturan->emailAdmin, $pengaturan->nama)->subject($subject2);
                });


                $akun = \OnlineAkun::where('akunId','=',$this->akunId)->get();
                $paypalbutton = "";
                if($preorder->jenisPembayaran==2){
                    //buat button paypal.
                    $paypal = new \GoPayPal(THIRD_PARTY_CART);
                    $paypal->sandbox = false;
                    $paypal->openInNewWindow = true;
                    $paypal->set('business', $akun[0]->acount);
                    $paypal->set('currency_code', 'USD');
                    $paypal->set('country', 'US');
                    $paypal->set('return', \URL::to('konfirmasiorder/'.$preorder->id));
                    $paypal->set('cancel_return', URL::to('konfirmasiorder/'.$preorder->id));
                    $paypal->set('notify_url', \URL::to('konfirmasiorder/'.$preorder->id)); # rm must be 2, need to be hosted online
                    $paypal->set('rm', 2); # return by POST
                    $paypal->set('no_note', 0);
                    $paypal->set('custom', \md5(time()));
                    $paypal->set('cbt', 'Return to our site to validate your payment!'); # caption override for "Return to Merchant" button                
                    $paypal->set('handling_cart', 1); # this overide the individual items' handling "handling_x"
                    $paypal->set('tax_cart', $akun[0]->fee);  
                    $item = new \GoPayPalCartItem();
                    $item->set('item_name', 'Payment for order : #'.$preorder->kodeOrder);
                    $item->set('item_number', '1');
                    $total = $preorder->total;
                    if($this->setting->mataUang == 1){
                        $total =round($preorder->total / \OnlineAkun::where('akunId','=',$this->akunId)->first()->rate); 
                    }
                    $item->set('amount', $total);
                    $item->set('quantity', 1);
                    $item->set('shipping', 0.1);
                    $item->set('handling', 1); # this is overriden by "handling_cart"
                    $paypal->addItem($item);
                    # If you set your custom button here, PayPal Pay Now button will be displayed.
                    $paypal->setButton('<button type="submit">Bayar Dengan Paypal - The safer, easier way to pay online!</button>');
                    $paypalbutton=$paypal->html();      
                }
                            
                \Shpcart::cart()->destroy();
                \Session::forget('diskonId');
                \Session::forget('besarPotongan');
                \Session::forget('tipe');
                \Session::forget('pengiriman');
                \Session::forget('pembayaran');
                \Session::forget('ekspedisiId');
                \Session::forget('ongkosKirim');
                \Session::forget('kodeunik');            
                
                if (@$akun[2]) 
                {
                    $this->layout->content = \View::make('jcheckout::po.postep5')->with('datapengirim' ,$datapengirim)
	                ->with('datapembayaran', $pembayaran)
	                ->with('preorder', $preorder)
	                ->with('banks' ,$bank_default)
	                ->with('banktrans', $bank_active)
	                ->with('paypal',  $akun[0])
	                ->with('creditcard' , $akun[1])
	                ->with('ipaymu' , $akun[2])
	                ->with('pengaturan', $this->setting)
	                ->with('paypalbutton', $paypalbutton)
	                ->with('kontak', $this->setting);
                }
                else
                {
                    $this->layout->content = \View::make('jcheckout::po.postep5')->with('datapengirim' ,$datapengirim)
	                ->with('datapembayaran', $pembayaran)
	                ->with('preorder', $preorder)
	                ->with('banks' ,$bank_default)
	                ->with('banktrans', $bank_active)
	                ->with('paypal',  $akun[0])
	                ->with('creditcard' , $akun[1])
	                ->with('pengaturan', $this->setting)
	                ->with('paypalbutton', $paypalbutton)
	                ->with('kontak', $this->setting);
                }
                

                $this->layout->seo = \View::make('jcheckout::seostuff')
                ->with('title',"Checkout - Finish - ".$this->setting->nama)
                ->with('description',$this->setting->deskripsi)
                ->with('keywords',$this->setting->keyword);
            }

        }
        elseif ($pengaturan->checkoutType==2) 
        {
            $inquiry = new \Inquiry;
            $inquiry->kodeInquiry = $kdOrder;
            $inquiry->pelangganId = $pelangganId;
            $inquiry->total= 0;
            $inquiry->status= 0;
            $inquiry->nama = $datapengirim['nama'];
            $inquiry->telp = $datapengirim['telp'];
            $inquiry->alamat = $datapengirim['alamat'];
            $inquiry->kota = \Kabupaten::find($datapengirim['kota'])->nama;    
            $inquiry->pesan = $datapengirim['pesan'];
            $inquiry->akunId = $this->akunId;
            $inquiry->save();

            if($inquiry)
            {
                //tambah det order
                $cart_contents = \Shpcart::wishlist()->contents();
                foreach ($cart_contents as $key => $value) {
                    $detinquiry = new \DetailInquiry;
                    $detinquiry->inquiryId=$inquiry->id;
                    $detinquiry->opsiSkuId= is_null($value['opsiskuId']) ? '':$value['opsiskuId'];
                    $detinquiry->produkId = $value['produkId'];
                    $detinquiry->qty = $value['qty'];
                    $detinquiry->created_at = date('Y-m-d H:m:s');
                    $detinquiry->updated_at = date('Y-m-d H:m:s');
                    $detinquiry->save();
                }
                //kirim email konfirmasi ke email user
                 $cart ='<table border="1" cellpadding="5"><tr>
                                <td>No</td>
                                <td>Nama Produk</td>
                                <td>Varian</td>
                                <td>Qty</td>
                            </tr>';            
                $cart = $cart.\View::make('admin.inquiry.listcartinquiry')->with('cart_contents', \Shpcart::wishlist()->contents())->With('berat','0');
                $cart = $cart."<tr>     
                            </tr></table>";
                \Shpcart::wishlist()->destroy();
                //kirim email order ke pelanggan
                $template = "<p>
                                    Halo {{pelanggan}}</p>
                                <p>
                                    Terimakasih telah berbelanja di {{toko}}.</p>
                                <p>
                                    Detail inquiry anda ID: {{kodeorder}},<br />
                                    Tanggal: {{tanggal}}<br />
                                    Detail Inquiry : {{cart}}</p>
                                <p>
                                    Inquiry anda akan kami proses sesegera mungkin</p>
                                <p>
                                    Salam Hangat, {{toko}}</p>
                                "; 
                $data = array(
                    'pelanggan'=> $inquiry->nama,
                    'pelangganalamat'=> $inquiry->alamat,
                    'pelangganphone'=> $inquiry->telp,
                    'toko' => $this->setting->nama,
                    'kodeorder' => $inquiry->kodeInquiry,
                    'tanggal' => date("Y-m-d"),
                    'cart' => $cart
                    );
                $datapengirim['fromemail']= $this->setting->email;
                $datapengirim['fromtoko']= $this->setting->nama;
                $email = bind_to_template($data,$template);            
                $subject = bind_to_template($data,'Konfirmasi Inquiry');  
                \Mail::later(3,'emails.email',array('data'=>$email), function($message) use ($subject,$datapengirim)
                {   
                    $message->from($datapengirim['fromemail'],$datapengirim['fromtoko']);
                    $message->to($datapengirim['email'], $datapengirim['nama'])->subject($subject);
                });
                //kirik email konfirmasi ke email toko
                $subject2 = 'Pemberitahuan Order -- '.bind_to_template($data,'Konfirmasi Inquiry');  
                \Mail::later(5,'emails.email',array('data'=>$email), function($message) use ($subject2,$pengaturan)
                {   
                    $message->to($pengaturan->emailAdmin, $pengaturan->nama)->subject($subject2);
                });
                \Shpcart::wishlist()->destroy();
                \Session::forget('tipe');
                \Session::forget('pengiriman');     
                
                $this->layout->content = \View::make('jcheckout::inquiry.inquiry4')->with('datapengirim' ,$datapengirim)
                ->with('inquiry', $inquiry)
                ->with('banktrans', $bank_active)
                ->with('pengaturan', $this->setting)
                ->with('kontak', $this->setting);

                $this->layout->seo = \View::make('jcheckout::seostuff')
                ->with('title',"Checkout - Finish - ".$this->setting->nama)
                ->with('description',$this->setting->deskripsi)
                ->with('keywords',$this->setting->keyword);
            }
        }
    }

    public function finpayDemo(){
    	return \View::make('jcheckout::finpay-demo')
    		->with('message','');
    }
    public function createFinPay($order = null)
    {
    	$order = \Order::find(1);
    	/* CREATE SIGNATURE */
		$mer_password = \Config::get('jcheckout::FinPay.merchant_password'); //IMPORTANT!
		$postdata = array(
			'merchant_id' => \Config::get('jcheckout::FinPay.merchant_id'),  //IMPORTANT!
			'invoice' => $order->kodeOrder,  //IMPORTANT!
			'amount' => $order->total,  //IMPORTANT!
			'add_info1' => 'Invoice No '.$order->kodeOrder,  //Customer Name //IMPORTANT!
			'add_info2' => '',
			'add_info3' => '',
			'add_info4' => '',
			'add_info5' => '',
			'timeout' => '12', //60 Menit (Expired Date)  //IMPORTANT!
			'return_url' => \Config::get('jcheckout::FinPay.RETURN_URL_195') //IMPORTANT! CHANGE THIS WITH YOUR RETURN TARGET URL!!!
		);
		$mer_signature =  mer_signature($postdata).$mer_password;  //IMPORTANT!
		/* END CREATE SIGANTURE */

		//INSERT DATA TO DATABASE
		$payment = new FinPay;
		$payment->invoice = $postdata["invoice"];
		$payment->amount = $postdata["amount"];
		$payment->add_info1 = $postdata["add_info1"];
		$payment->add_info2 = '';
		$payment->add_info3 = '';
		$payment->add_info4 = '';
		$payment->add_info5 = '';
		$payment->timeout = $postdata["timeout"];
		$payment->return_url = $postdata["return_url"];
		$payment->save();
	
		//SENT DATA VIA CURL
		$respon = curl_post(\Config::get('jcheckout::FinPay.REQUEST_URL_195'), $postdata);

		//echo $respon;
		//if respon code 00 is Success, antoher respond code is Failed	

		//SELECT DATA INTO DB IF RESPON IS "00"
		if($respon=='00'){
			$finpay = FinPay::where('invoice',$postdata['invoice'])->orderBy('id','desc')->first();
			$rs = array('error'=>false,'response'=>$respon,'message'=>'Successfuly create new payment');
		}else{
			$rs = array('error'=>true,'response'=>$respon,'message'=>'Unuccessfuly create new payment');
		}
		return $rs;
    }

    public function checkFinPay()
    {
    	/* CREATE SIGNATURE */
		$mer_password = \Config::get('jcheckout::FinPay.merchant_password'); //IMPORTANT!
		$postdata = array(
			'merchant_id' => \Config::get('jcheckout::FinPay.merchant_id'),  //IMPORTANT!
			'payment_code' => $_POST["payment_code"],  //IMPORTANT!
			'return_url' => \Config::get('jcheckout::FinPay.RETURN_URL_195') //IMPORTANT! CHANGE THIS WITH YOUR RETURN TARGET URL!!!
		);
		$mer_signature =  mer_signature($postdata).$mer_password;  //IMPORTANT!
		/* END CREATE SIGANTURE */
		
		/* DATA FOR SENT */
		$postdata = array(
			'mer_signature' => hash256($mer_signature),  //IMPORTANT!
			'merchant_id' => $postdata['merchant_id'],  //IMPORTANT!
			'payment_code' => $postdata['payment_code'],  //IMPORTANT!
			'return_url' => $postdata['return_url'] //IMPORTANT!
		);
		/* END DATA FOR SENT */

		//SENT DATA VIA CURL
		$respon = curl_post(\Config::get('jcheckout::FinPay.CHECK_STATUS_URL_195'), $postdata);

		$status = array();
		if($respon=="00"){ //PAID
			//DO ACTION WITH YOUR CONDITION
			$status['response'] = "PAID";
		}else if($respon=="04"){ //UNPAID
			//DO ACTION WITH YOUR CONDITION
			$status['response'] = "UNPAID";
		}else if($respon=="05"){ //EXPIRED
			//DO ACTION WITH YOUR CONDITION
			$status['response'] = "EXPIRED";
		}else if($respon=="06"){ //EXPIRED
			//DO ACTION WITH YOUR CONDITION
			$status['response'] = "CANCEL";
		}else if($respon=="14"){ //NOT FOUND
			//DO ACTION WITH YOUR CONDITION
			$status['response'] = "NOT FOUND";
		}else{ //NOT FOUND
			//DO ACTION WITH YOUR CONDITION
			$status['response'] = $respon;
		}
		return $status;
    }

    public function cancelFinPay()
    {
    	/* CREATE SIGNATURE */
		$mer_password = \Config::get('jcheckout::FinPay.merchant_password'); //IMPORTANT!
		$postdata = array(
			'merchant_id' => \Config::get('jcheckout::FinPay.merchant_id'),  //IMPORTANT!
			'payment_code' => $_POST["payment_code"],  //IMPORTANT!
			'return_url' => \Config::get('jcheckout::FinPay.RETURN_URL_195') //IMPORTANT! CHANGE THIS WITH YOUR RETURN TARGET URL!!!
		);
		$mer_signature =  mer_signature($postdata).$mer_password;  //IMPORTANT!
		/* END CREATE SIGANTURE */
		
		/* DATA FOR SENT */
		$postdata = array(
			'mer_signature' => hash256($mer_signature),  //IMPORTANT!
			'merchant_id' => $postdata['merchant_id'],  //IMPORTANT!
			'payment_code' => $postdata['payment_code'],  //IMPORTANT!
			'return_url' => $postdata['return_url'] //IMPORTANT!
		);
		/* END DATA FOR SENT */

		//SENT DATA VIA CURL
		$respon = curl_post(\Config::get('jcheckout::FinPay.CANCEL_URL_195'), $postdata);
		
		$status = array();
		if($respon=="00"){ //CANCEL IS SUCCESS
			//DO ACTION WITH YOUR CONDITION
			$status['response']= "CANCEL IS SUCCESS";
		}else if($respon=="88"){ //CANCEL IS FAILED BECOUSE ALREADY PAID
			//DO ACTION WITH YOUR CONDITION
			$status['response']= "CANCEL IS FAILED BECOUSE ALREADY PAID";
		}else if($respon=="05"){ //CANCEL IS FAILED BECOUSE ALREADY PAID
			//DO ACTION WITH YOUR CONDITION
			$status['response']= "CANCEL IS FAILED BECOUSE ALREADY EXPIRED";
		}else if($respon=="06"){ //CANCEL IS FAILED BECOUSE ALREADY PAID
			//DO ACTION WITH YOUR CONDITION
			$status['response']= "CANCEL IS FAILED BECOUSE ALREADY CANCEL";
		}else if($respon=="14"){ //NOT FOUND
			//DO ACTION WITH YOUR CONDITION
			$status['response']= "NOT FOUND";
		}else{
			//DO ACTION WITH YOUR CONDITION
			$status['response']= $respon;
		}
		return $status;
    }
    public function getFinPayResponse()
    {
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

    }
}
<?php namespace Bcscoder\Jcheckout;

class JcheckoutCartController extends JcheckoutBaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {     
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        //check member
        $reseller = 0;
        if ($this->pengaturan->checkoutType==1)
        {
            if (Sentry::check())
                {            
                    $user = Sentry::getUser();
                    if($user->tipe==2){
                        $reseller=1;
                    }
                }
        }
        // Populate a proper item array. 
        $produkId = urldecode(Input::get('produkId'));
        $name = str_replace('-', ' ', $produkId);        
        $produk= Produk::where('slug','=',$produkId)->where('akunId','=',$this->akunId)->get();
        if($produk->count()==0){            
            $produk = Produk::where('nama','like','%'.$name.'%')->where('akunId','=',$this->akunId)->get();
        }
        if($produk->count()==1)
        {
            $produkId = $produk->first()->id;
        }
        else
        {
            return 0;
        }
        $opsiid = is_null(Input::get('opsi')) ? '0':Input::get('opsi');

        
        $option =array();
        $qty = Input::get('qty');
        //cek stok
        if ($this->pengaturan->checkoutType!=2) 
        {
            
            if ($this->pengaturan->checkoutType==3) 
            {
                Shpcart::cart()->destroy();
            }

            $cart_contents = Shpcart::cart()->contents();
            $qtycek = 0;

            foreach ($cart_contents as $key => $value) 
            {
                if($value['produkId']==$produkId && $value['opsiskuId']==$opsiid)
                {
                    $qtycek = $value['qty'];
                }
            }

            if($opsiid=='0')
            {
                $stok = Produk::find($produkId);
                if($reseller==1 && $stok->hargaMitra!=0)
                {
                    $harga = Produk::find($produkId)->hargaMitra;                
                }
                else
                {
                    $harga = Produk::find($produkId)->hargaJual;            
                }
                
                if ($this->pengaturan->checkoutType==1)
                {
                    if($stok->stok<($qty+$qtycek))
                    {
                        //stok kurang
                        return 'stok';
                    }
                }
            }
            else
            {
                $findsku = Opsisku::find($opsiid);                 
                if(!is_null($findsku))
                {
                    $option['opsi']= $findsku->opsi1.($findsku->opsi2=='' ? '':' / '.$findsku->opsi2).($findsku->opsi3=='' ?'':' / '.$findsku->opsi3);
                    $harga = $findsku->harga;
                }
                else
                {
                    return 'opsi';
                }
                
                if ($this->pengaturan->checkoutType==1)
                {
                    if($findsku->stok<($qty+$qtycek))
                    {
                        //stok kurang
                        return 'stok';
                    }
                }
            }
        }
        else
        {
            if($opsiid!='0')
            {
                $findsku = Opsisku::find($opsiid);                 
                if(!is_null($findsku))
                {
                    $option['opsi']= $findsku->opsi1.($findsku->opsi2=='' ? '':' / '.$findsku->opsi2).($findsku->opsi3=='' ?'':' / '.$findsku->opsi3);
                    $harga = $findsku->harga;
                }
                else
                {
                    return 'opsi';
                }
            }
            $harga = 0;
        }

        $produk = Produk::find($produkId);

        $item = array(
            'id'      => $produkId.'_'.$opsiid,
            'produkId' => $produkId,
            'opsiskuId' => $opsiid,
            'qty'     => $qty,
            'price'   => $harga,
            'name'    => $produk->nama,
            'image'   => $produk->gambar1,
            'berat'   => $produk->berat,
            'options' => $option
        );

        if ($this->pengaturan->checkoutType!=2)
        {
            $rowid = Shpcart::cart()->insert($item);

            if ($this->pengaturan->checkoutType==3) 
            {
                $data['url'] = URL::to('');
                return Response::json($data);
            }
        }
        else 
        {
            $rowid = Shpcart::wishlist()->insert($item);
        }

        //reload shopping cart
        $tema = Tema::where('akunId','=',$this->akunId)->where('status','=', '1')->get();        
        $namaTema = $tema[0]->nama;
        $theme = Theme::theme($namaTema)->layout($namaTema)->setTitle($this->pengaturan->nama);
        $data = array();
        if ($this->pengaturan->checkoutType!=2)
        {
            $data['cart']   = Theme::widget('WidgetShopingcart', array('label' => 'Demo Widget'))->render();
            $data['detail'] =  '<div class="cart_dialog">
                                    <div class="content">
                                        <div class="mini-cart-info">
                                            <table class="cart">
                                            <tbody>
                                                <tr>
                                                    <td colspan="5">1 produk berhasil ditambahkan ke keranjang belanja Anda</td>
                                                </tr>
                                                <tr>
                                                    <td class="image"><img title="'.$item['name'].'" alt="'.$item['name'].'" src="'.URL::to(getPrefixDomain().'/produk/thumb/'.$item['image']).'" width="75" height="75"></a></td>
                                                    <td class="name" valign="middle"><a href="'.URL::to('produk/'.$item['produkId'].'-'.Str::slug($item['name'])).'">'.$item['name'].'</a></td>
                                                   <td class="quantity">x&nbsp;'.$item['qty'].'</td>
                                                   <td class="total">'.price_format($item['qty']*$item['price']).'</td>
                                                   <td class="remove"><a href="javascript:deletecartdialog('."'".$rowid."'".')">x</a></td>
                                                </tr>               
                                            </tbody>
                                            </table>
                                        </div>    
                                       <div style="background-color: #F2F4F3; border-bottom: 1px solid #dfe0e2;font-size:12px">
                                            <div style="padding:10px 0;border-top: 1px solid #FFF; border-bottom: 1px solid #FFF; padding-left: 10px;">Total Nilai Belanja ( '.Shpcart::cart()->total_items().' produk ) : '.price_format(Shpcart::cart()->total() ).'</div>        
                                        </div>
                                        <br><br>
                                        <div class="button-dialogs" style="margin-bottom:0px">
                                          <div class="right"><a class="button-dialog" href="'.URL::to('checkout').'">Keranjang &rsaquo;</a></div>
                                          <div class="left"><a class="button-dialog" href="javascript:close_dialog()">&lsaquo; Lanjut Belanja</a></div>
                                        </div>
                                    </div>
                                </div>';
        }
        else
        {
            $data['cart']   = Theme::widget('WidgetInquirycart', array('label' => 'Demo Widget'))->render();
            $data['detail'] =  '<div class="cart_dialog">
                                    <div class="content">
                                        <div class="mini-cart-info">
                                            <table class="cart">
                                            <tbody>
                                                <tr>
                                                    <td colspan="4">1 produk berhasil ditambahkan ke keranjang belanja Anda</td>
                                                </tr>
                                                <tr>
                                                    <td class="image"><img title="'.$item['name'].'" alt="'.$item['name'].'" src="'.URL::to(getPrefixDomain().'/produk/thumb/'.$item['image']).'" width="75" height="75"></a></td>
                                                    <td class="name" valign="middle"><a href="'.URL::to('produk/'.$item['produkId'].'-'.Str::slug($item['name'])).'">'.$item['name'].'</a></td>
                                                    <td class="quantity">x&nbsp;'.$item['qty'].'</td>
                                                   <td class="remove"><a href="javascript:deletecartdialog('."'".$rowid."'".')">x</a></td>
                                                </tr>               
                                            </tbody>
                                            </table>
                                        </div>    
                                       <div style="background-color: #F2F4F3; border-bottom: 1px solid #dfe0e2;font-size:12px">
                                            <div style="padding:10px 0;border-top: 1px solid #FFF; border-bottom: 1px solid #FFF; padding-left: 10px;">Total Belanja ( '.Shpcart::wishlist()->total_items().' produk ) : </div>        
                                        </div>
                                        <br><br>
                                        <div class="button-dialogs" style="margin-bottom:0px">
                                          <div class="right"><a class="button-dialog" href="'.URL::to('checkout').'">Keranjang &rsaquo;</a></div>
                                          <div class="left"><a class="button-dialog" href="javascript:close_dialog()">&lsaquo; Lanjut Belanja</a></div>
                                        </div>
                                    </div>
                                </div>';
        }
        return Response::json($data);
    
        return 'po';
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        return $id;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {

        $items = array();
        $qty = \Input::get('qty');
        
        if ($this->setting->checkoutType!=2) 
        {
            $cart = \Shpcart::cart()->item($id);  

            if($cart['opsiskuId']!=0){
                $stok = \Opsisku::find($cart['opsiskuId'])->stok;
            }else{
                $stok = \Produk::find($cart['produkId'])->stok;
            }
             //cek qty yg diminta

            if ($this->setting->checkoutType==1) 
            {
                if($qty<=$stok){
                    $items = array(
                        'rowid' => $id,
                        'qty'   => $qty
                    );
                }
            }
            else
            {
                $items = array(
                        'rowid' => $id,
                        'qty'   => $qty
                    );
            }
        }      
        else 
        {
            $cart = \Shpcart::wishlist()->item($id); 
            $items = array(
                    'rowid' => $id,
                    'qty'   => $qty
                ); 
        }
                            
        // Update the cart contents.
        //
        if(count($items)>0){
            if ($this->setting->checkoutType!=2) 
            {
                \Shpcart::cart()->update($items);                
                $cart = \Shpcart::cart()->item($id);
                $data= array();
                $data['current_price'] = $cart['price']*$cart['qty'];
                $data['total_price'] = \Shpcart::cart()->total();
                
                return \Response::json($data);
            }
            else
            {
                \Shpcart::wishlist()->update($items);
                $cart = \Shpcart::wishlist()->item($id);
                return $cart['qty'].';'.\Shpcart::wishlist()->total_items();
            }
        }else{
            return 'false';
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
    public function delete($id)
    {
        if ($this->pengaturan->checkoutType==1) 
        {
            Shpcart::cart()->remove($id);

            if (Request::ajax())
            {   
                $data['total'] = price_format(Shpcart::cart()->total());
                $data['jumlah'] = Shpcart::cart()->total_items();
                $data['cart'] = Theme::widget('WidgetShopingcart', array('label' => 'Demo Widget'))->render();
                return Response::json($data);
            }
        }      
        elseif ($this->pengaturan->checkoutType==2) 
        {
            Shpcart::wishlist()->remove($id);
            if (Request::ajax())
            {   
                $data['total'] = price_format(Shpcart::wishlist()->total());
                $data['jumlah'] = Shpcart::wishlist()->total_items();
                $data['cart'] = Theme::widget('WidgetInquirycart', array('label' => 'Demo Widget'))->render();
                return Response::json($data);
            }
        }
        return Redirect::to('cart');
    }
    public function checkdiskon($id){  
        //if (Sentry::check())
        //{
            if(\Input::get('status')=='cancel'){
                \Session::forget('diskonId');
                \Session::forget('besarPotongan');
                \Session::forget('tipe');
                return 'true';
            }
            $minBuy = \Shpcart::cart()->total();
            $diskon = \Diskon::where('akunId','=',$this->akunId)->where('kode','=',$id)->first();;
            if($diskon==null){
                return \Response::json(array('error' =>1,'message'=> 'Maaf, Kupon diskon tidak ditemukan.'));
            }else{

                //cek masa berlaku
                if(($diskon->tglBerakhir<= date('Y-m-d') && $diskon->tglMulai>= date('Y-m-d'))){
                    return \Response::json(array('error' =>1,'message'=>'Maaf, Masa berlaku kupon diskon anda sudah habis.'));
                }
                //cek sisa klaim
                if($diskon->jumlah<=$diskon->klaim){
                    return \Response::json(array('error' =>1,'message'=>'Maaf, kupon sudah tidak tersedia.'));
                }
                //cek produk yg di diskon
                if($diskon->produkId==0){
                    if($minBuy<$diskon->minBuy){
                         return \Response::json(array('error' =>1,'message'=>'Maaf, Order Tidak Memenuhi minimal belanja sebesar '.$diskon->minBuy.'.'));
                    }else{
                        $total = \Shpcart::cart()->total();                        
                        if($total==0){
                            return \Response::json(array('error' =>1,'message'=>'Maaf, Diskon tidak ditemukan.'));
                        }else if($total<$diskon->minBuy){
                            return \Response::json(array('error' =>1,'message'=>'Maaf, Order Tidak Memenuhi minimal belanja sebesar '.$diskon->minBuy.'.'));
                        }
                        $potongan=$diskon->besarPotongan;
                        if($diskon->jenisPotongan==2){
                            $potongan = $diskon->besarPotongan * $total /100;
                        }                
                        \Session::put('diskonId', $diskon->id);
                        \Session::put('besarPotongan', $diskon->besarPotongan);
                        \Session::put('tipe', $diskon->jenisPotongan);                    
                        return \Response::json(array('error'=>0,'success'=>1,'diskonId'=>$diskon->id,'potongan'=>$potongan,'type'=>$diskon->jenisPotongan,"besarPotongan"=>$diskon->besarPotongan));
                    }
                }else if($diskon->produkId!=-1 && $diskon->produkId!=0){
                    $total = 0;
                    foreach (\Shpcart::cart()->contents() as $key => $value) {
                        if($value['produkId']==$diskon->produkId){
                            //cek min belanja
                            $total+= ($value['qty']*$value['price']);                                              
                        }
                    }
                    if($total==0){
                        return \Response::json(array('error' =>1,'message'=>'Maaf, Diskon tidak ditemukan.'));
                    }else if($total<$diskon->minBuy){
                        return \Response::json(array('error' =>1,'message'=>'Maaf, Order Tidak Memenuhi minimal belanja sebesar '.$diskon->minBuy.'.'));
                    }
                    $potongan=$diskon->besarPotongan;
                    if($diskon->jenisPotongan==2){
                        $potongan = $diskon->besarPotongan * $total /100;
                    }                
                    \Session::put('diskonId', $diskon->id);
                    \Session::put('besarPotongan', $diskon->besarPotongan);
                    \Session::put('tipe', $diskon->jenisPotongan);                    
                    return \Response::json(array('error'=>0,'success'=>1,'diskonId'=>$diskon->id,'potongan'=>$potongan,'type'=>$diskon->jenisPotongan,"besarPotongan"=>$diskon->besarPotongan));
                }
                // cek diskon per produk
                
                //cek diskon perkategori
                else if($diskon->kategoriId!=-1 && $diskon->kategoriId!=0){
                    //cari kategori produknya
                    $total = 0;
                    foreach (\Shpcart::cart()->contents() as $key => $value) {
                        $cat = \Produk::find($value['produkId'])->kategori->id;

                        //anaknya kita cari
                        $kategori = \Kategori::find($diskon->kategoriId);
                        if($diskon->kategoriId==$cat){
                            $total = $total+ $value['qty']*$value['price'];
                        }
                        else if($kategori->anak!=null){
                            foreach ($kategori->anak as $anak) {
                                if($anak->id==$cat){
                                    $total = $total+ $value['qty']*$value['price'];
                                }
                                if($anak->anak!=null){
                                    foreach ($anak->anak as $anak2) {
                                        if($anak2->id==$cat){
                                            $total = $total+ $value['qty']*$value['price'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if($total==0){
                       return \Response::json(array('error' =>1,'message'=>'Maaf, Kupon diskon tidak ditemukan.'));
                    }else if($total<$diskon->minBuy){
                        return \Response::json(array('error' =>1,'message'=>'Maaf, Order Tidak Memenuhi minimal belanja sebesar '.$diskon->minBuy.'.'));
                    }
                    $potongan=$diskon->besarPotongan;
                    if($diskon->jenisPotongan==2){
                        $potongan = $diskon->besarPotongan * $total /100;
                    }               

                    \Session::put('diskonId', $diskon->id);
                    \Session::put('besarPotongan', $diskon->besarPotongan);
                    \Session::put('tipe', $diskon->jenisPotongan); 
                    return \Response::json(array('error'=>0,'success'=>1,'diskonId'=>$diskon->id,'potongan'=>$potongan,'type'=>$diskon->jenisPotongan,"besarPotongan"=>$diskon->besarPotongan));
                }
                //cek perkoleksi
                else if($diskon->koleksiId!=-1 and $diskon->koleksiId!=0){
                    $total =0;
                    foreach (\Shpcart::cart()->contents() as $key => $value) {
                        $pro = \Produk::find($value['produkId'])->koleksiId;
                        if($pro->koleksiId!=$diskon->koleksiId){
                            $total += $value['qty']*$value['price'];
                        }
                    }
                    if($total==0){
                        return \Response::json(array('error' =>1,'message'=>'Maaf, Kupon diskon tidak ditemukan.'));
                    }else if($total<$diskon->minBuy){
                        return \Response::json(array('error' =>1,'message'=>'Maaf, Order Tidak Memenuhi minimal belanja sebesar '.$diskon->minBuy.'.'));
                    }
                    $potongan=$diskon->besarPotongan;
                    if($diskon->jenisPotongan==2){
                        $potongan = $diskon->besarPotongan * $total /100;
                    }              
                    
                    \Session::put('diskonId', $diskon->id);
                    \Session::put('besarPotongan', $diskon->besarPotongan);
                    \Session::put('tipe', $diskon->jenisPotongan);  
                    return \Response::json(array('error'=>0,'success'=>1,'diskonId'=>$diskon->id,'potongan'=>$potongan,'type'=>$diskon->jenisPotongan,"besarPotongan"=>$diskon->besarPotongan));
                }
            }
        //}else{
            //return Response::json(array('error' =>'Maaf, anda harus menjadi login untuk dapat menggunakan kupon diskon.'));
        //} 
    }
    function checkekspedisi($id){
        $cart = \Shpcart::cart()->contents();
        $berat = 0;
        foreach ($cart as $value){
            $beratnew = \Produk::find($value['produkId'])->berat;
            $berat = $berat+ ($value['qty']*$beratnew);
        }

        $berat = ceil($berat/1000);
        $html = '<hr>';
        if($berat!=0){            
            $statusApi = $this->setting->statusApi;
            $result = null;
            $resultTiki =null;
            if($statusApi==1){    
                $result = \Ongkir::getCost($this->setting->kotaAsal,$id,$berat);
                $layanan = explode(';', $this->setting->layananEkspedisi);
            }
            if($statusApi==2){    
                //tiki error
                $resultTiki = \Tiki::getCost($this->setting->kotaAsal,$id,$berat);
            }
            if($result!=null){
                $prices = $result['price'];
                $city = $result['city'];
                foreach ($prices->item as $item)
                {
                    if(in_array($item->service_code,$layanan)){

                    $html = $html. '<label class="radio">
                                    <input type="radio" style="margin-left:0;margin-right:10px" name="ekspedisilist" value="JNE '.$item->service.';'.$item->value.'">
                                   <small> JNE '.$item->service.' harga: '.price_format($item->value).'</small>
                                </label><br>';
                    }
                }
            }elseif($resultTiki!=null){    
                foreach ($resultTiki as $hasil)
                {
                    //echo 'Layanan: ' . $hasil['layanan'] . ', dengan harga : Rp. ' . $hasil['harga'] . ',- <br />';
                    $html = $html. '<label class="radio">
                                <input type="radio" style="margin-left:0;margin-right:10px" name="ekspedisilist" value="Tiki '.$hasil['layanan'].';'.$hasil['harga'].'">
                               <small> TIKI '.$hasil["layanan"].' harga: Rp '.$hasil["harga"].'</small>
                            </label><br>';
                }
            }
            $tarif = \Tarif::join('paket','tarif.paketId','=','paket.id')
                ->whereRaw('(tarif.tujuan ="'.$id.'" or tarif.tujuan LIKE "%'.strtolower($id).'%" or tarif.tujuan LIKE "%'.strtoupper($id).'%" or tarif.tujuan LIKE "%'.ucfirst($id).'%") and paket.akunId='.$this->akunId)
                ->get();
            foreach ($tarif as $key => $value) {
                $html = $html. '<label class="radio">
                                    <input type="radio" style="margin-left:0;margin-right:10px" name="ekspedisilist" value="'.$value->paket->ekspedisi->nama.' '.$value->nama.';'.$value->harga*$berat.'">
                                    <small>'.$value->paket->ekspedisi->nama.' '.$value->paket->nama.' - '.price_format($value->harga*$berat).'</small>
                                </label><br>';
            }

            if(($resultTiki==null && $result==null) && $tarif->count()==0){
                $html= $html.'<p>Tidak ditemukan ekpedisi dari <strong>'.$this->setting->kotaAsal.'</strong> ke tujuan : <strong>'.$id.'</strong> <br>
                    <small><i>untuk informasi pengiriman silakan hubungi kami <a href="'.URL::to('kontak').'">disini</a></i></small>
                </p>';
            }else{
                $html = '<p>Ekspedisi list dari <strong>'.$this->setting->kotaAsal.'</strong> ke tujuan: <strong>'.$id.'</strong> ('.$berat.' Kg)</p>'.$html;
            }
        }
        return '<div id="result_ekspedisi">'.$html.'<hr></div>';
    }
    function addekspedisi($id){
        $eks = explode(';',$id);
        Session::put('tujuan',Input::get('tujuan'));
        Session::put('ekspedisiId', $eks[0]);
        Session::put('ongkosKirim', $eks[1]);
        return Session::get('ekspedisiId');
    }

    function pengiriman(){
        //check session cart dan ekspedisi dan diskon                
        if(Shpcart::cart()->total_items()==0 || !(Session::has('ekspedisiId'))) {
            return Redirect::to('checkout');
        }
       //check ekspedisi
        if($this->pengaturan->statusEkspedisi==1){
            if(!Session::has('ekspedisiId')){
                 return Redirect::to('checkout');
            }
        }

        // untuk filter product yg ditampilkan
        $produk = Koleksi::where('akunId', '=', $this->akunId)->get();       
        $view = array(
            'cart' => Shpcart::cart(),
            'provinsi' => Provinsi::where('negaraId','=',Pengaturan::where('akunId', '=', $this->akunId)->first()->negara)->get(),
            'user' => (Sentry::check() ? Sentry::getUser():''),
            'negara' => Negara::lists('nama','id'),
            'provinsi' => Provinsi::lists('nama','id'),
            'kota' => Kabupaten::lists('nama','id')
        );
        $this->theme->partialComposer('seostuff', function($view)
        {
            $view->with('title', Pengaturan::find(1)->nama.' - Data Pengiriman')
                ->with('description',Pengaturan::find(1)->deskripsi)
                ->with('keywords',Pengaturan::find(1)->keyword);
        });
        if(Session::has('message')){            
            echo "<div class='".Session::get('message')."' id='message' style='display:none'>
                <p>".Session::get('text')."</p>
            </div>";
        }
        return $this->theme->scope('checkout.pengiriman', $view)->render();
    }

    function pembayaran(){
        if ( ! Sentry::check()){
            try
            {
                $user = Sentry::getUserProvider()->findByCredentials(array(
                    'email'      => Input::get('email'),
                    'tipe' => '1'
                ));
                if($user){
                    return Redirect::to('pengiriman')->withInput()->with('message','error')->with('text','Alamat email sudah digunakan. Coba yang lain atau silakan login.');
                }
            }

            catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
            {
                try
                {
                    $user = Sentry::getUserProvider()->findByCredentials(array(
                        'email'      => Input::get('email'),
                        'tipe' => '2'
                    ));
                    if($user){
                        return Redirect::to('pengiriman')->withInput()->with('message','error')->with('text','Alamat email sudah digunakan. Coba yang lain atau silakan login.');
                    }
                }

                catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
                {
                    echo '';
                }
            }

        }
        
        Session::put('pengiriman', Input::all());       
        $akun = OnlineAkun::where('akunId', '=', $this->akunId)->get();
        $view = array(
            'cart' => Shpcart::cart(),                        
            'banks' => BankDefault::all(),
            'banktrans' => Bank::where('akunId', '=', $this->akunId)->get(),
            'paypal' =>  $akun[0],
            'creditcard' => $akun[1]
        );    
        $this->theme->partialComposer('seostuff', function($view)
        {
            $view->with('title', Pengaturan::where('akunId', '=', $this->akunId)->first()->nama.' - Data Pembayaran')
                ->with('description',Pengaturan::where('akunId', '=', $this->akunId)->first()->deskripsi)
                ->with('keywords',Pengaturan::where('akunId', '=', $this->akunId)->first()->keyword);
        });
        return $this->theme->scope('checkout.pembayaran', $view)->render();
    }
    function konfirmasi(){

        Session::put('pembayaran',Input::all());
        $pembayaran = Input::all();
        $datapengirim = Session::get('pengiriman');
        $datapengirim['negara'] = Negara::find($datapengirim['negara'])->nama;
        $datapengirim['provinsi'] = Provinsi::find($datapengirim['provinsi'])->nama;
        $datapengirim['kota'] = Kabupaten::find($datapengirim['kota'])->nama;
    
        $akun = OnlineAkun::all();
        $potongan = 0;
        
        if(!is_null(Session::get('diskonId'))){
            if(Session::get('tipe')==1){
                $potongan = Session::get('besarPotongan');                
            }else{
                $potongan = (Shpcart::cart()->total()*Session::get('besarPotongan')/100);
            }
        }
        $total = (Shpcart::cart()->total() + Session::get('ongkosKirim')- $potongan);        
        $total = $total + (Pajak::all()->first()->status==0 ? 0 : $total * Pajak::all()->first()->pajak / 100) + Session::get('kodeunik');        
        $view = array(
            'cart' => Shpcart::cart(),
            'datapengirim' => $datapengirim,
            'dataekspedisi' => Session::get('ekspedisiId'),
            'datapembayaran' => $pembayaran,
            'kodekupon' => Session::has('diskonId') ? Diskon::find(Session::get('diskonId'))->kode : '',
            'kodeunik' => Session::get('kodeunik'),
            'diskon' => $potongan,
            'total' => $total
        );
        $this->theme->partialComposer('seostuff', function($view)
        {
            $view->with('title', Pengaturan::where('akunId', '=', $this->akunId)->first()->nama.' - Konfirmasi Detail Order')
                ->with('description',Pengaturan::where('akunId', '=', $this->akunId)->first()->deskripsi)
                ->with('keywords',Pengaturan::where('akunId', '=', $this->akunId)->first()->keyword);
        });
        return $this->theme->scope('checkout.konfirmasi', $view)->render();
    }
    
    public function getProvinsi($id)
    {
        $pro = \Negara::remember(5)->find($id)->provinsi;
        return \Response::json($pro);
    }
    public function getKabupaten($id)
    {
        $pro = \Provinsi::remember(5)->find($id)->kabupaten;
        return \Response::json($pro);
    }
}
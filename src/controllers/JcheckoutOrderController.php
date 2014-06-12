<?php namespace Bcscoder\Jcheckout;

class JcheckoutOrderController extends JcheckoutBaseController {
    
    public function index()
    {
        $this->layout->content = \View::make('jcheckout::konfirmasi-order'); 
    }
    public function store()
    {
        $kodeorder = \Input::get('kode_order');
        $order =\Order::where('kodeOrder','=',$kodeorder)->first();

        if($order)
        {
            return \Redirect::to('mycheckout/konfirmasi-order/'.$order->id);
        }
        else
        {
            return \Redirect::to('mycheckout/konfirmasi-order')
            ->with('error','Order tidak ditemukan. Silakan coba kode order yang lain.');
        }
    }
    public function show($id)
    {
        $order = \Order::find($id);
        if($order)
        {
            $paypalbutton ='';
            $payment = '';
            if($order->jenisPembayaran==2)
            {
                $rate = \OnlineAkun::where('akunId','=',$this->akunId)->first();
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
                    $total =round($order->total / $rate->rate); 
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
            else if($order->jenisPembayaran==6)
            {
                $payment = FinPay::where('invoice','=',$order->kodeOrder)->first();
            }
            $bank_active = \Bank::where('akunId','=',$this->akunId)->where('status','=',1)->get(); 
            $this->layout->content = \View::make('jcheckout::konfirmasi-order')
                ->with('order',$order)
                ->with('bank_active',$bank_active)
                ->with('paypal_button',$paypalbutton)
                ->with('jarvis_payment',$payment);
        }
    }
    public function update($id)
    {
        $rules = array(     

            'nama' => 'required',

            'noRekPengirim' => 'required',                  

            'bank' => 'required',

            'jumlah' => 'required|integer'

        ); 
        $messages = array(
            'integer' => 'Masukan harga sesuai jumlah yang belum dibayar !',
        );
        $validation = \Validator::make(\Input::all(),$rules,$messages);

        if($validation->fails()){               

            return \Redirect::to('mycheckout/konfirmasi-order/'.$id)->withErrors($validation)->withInput();             

        }

        //cek apakah konfirmasi udah ada

        $kon = \Konfirmasi::where('orderId','=',$id)->get();

        if($kon->count()>0){

            $konfirmasi = \Konfirmasi::find($kon->first()->id);

            $konfirmasi->nama = \Input::get('nama');

            $konfirmasi->noRekPengirim = \Input::get('noRekPengirim');

            $konfirmasi->bankId = \Input::get('bank');

            $konfirmasi->jumlah = \Input::get('jumlah');

            $konfirmasi->orderId = $id;

            $konfirmasi->tanggal = date('Y-m-d H:m:s');

            $konfirmasi->status = 0;

            $konfirmasi->save();

        }else{

            $konfirmasi =  new \Konfirmasi;

            $konfirmasi->nama = \Input::get('nama');

            $konfirmasi->noRekPengirim = \Input::get('noRekPengirim');

            $konfirmasi->bankId = \Input::get('bank');

            $konfirmasi->jumlah = \Input::get('jumlah');

            $konfirmasi->orderId = $id;

            $konfirmasi->tanggal = date('Y-m-d H:m:s');

            $konfirmasi->status = 0;

            $konfirmasi->save();

        }

        //update order status

        $order = \Order::find($id);

        $order->status= 1;

        $order->save();

        //kirim email konfirmasi order

        //kirim email konfirmasi pembayaran 

         $data = array(

            'pelanggan'=> $order->nama,

            'toko' => $this->setting->nama,

            'kodeorder' => $order->kodeOrder,

            'tanggal' => $order->tanggalOrder,

            'namaPengirim' => $order->konfirmasi==null ? '-':$order->konfirmasi->nama,

            'noRekening' => $order->konfirmasi==null ? '-':$order->konfirmasi->noRekPengirim,

            'rekeningTujuan' =>$order->konfirmasi==null ? '-' : $order->konfirmasi->bank->bankdefault->nama.' - '.$order->konfirmasi->bank->noRekening.' a/n '.$order->konfirmasi->bank->atasNama,

            'jumlah' =>$order->konfirmasi==null ? '-':price_format($order->konfirmasi->jumlah),

            'cart' => \View::make('admin.order.detailorder')->with('order',$order),

            'namaEkspedisi' => $order->jenisPengiriman,

            'noResi' => $order->noResi,

            'tujuanPengiriman' => $order->alamat.' - '.$order->kota,

            'linkRegistrasi' => \URL::to('member/create')

            );

        $template = \Templateemail::where('akunId','=',$this->akunId)->where('no','=',6)->first();            
        $template_email = \View::make('jcheckout::email.konfirmasi-order');
        $email = bind_to_template($data,$template_email);            

        $subject = bind_to_template($data,$template->judul);  
        $toko = $this->setting;
        $order->fromemail=$toko->emailAdmin;
        $order->fromtoko=$toko->nama;
        $a = \Mail::later(1,'jcheckout::email.send',array('data'=>$email), function($message) use ($subject,$order)
        {   
            $message->from($order->fromemail,$order->fromtoko);
            $message->to($order->pelanggan->email, $order->pelanggan->nama)->subject($subject);
        });

        $a = \Mail::later(2,'jcheckout::email.send',array('data'=>$email), function($message) use ($subject,$toko)
        {   
            $message->to($toko->emailAdmin,$toko->nama )->subject($subject);
        });

        return \Redirect::to('mycheckout/konfirmasi-order/'.$id)->with('success',true);
    }
}
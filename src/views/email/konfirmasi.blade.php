<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Order Notification</title>
        <style type="text/css">
        body {margin: 0; padding: 0; min-width: 100%!important;}
        .content {width: 100%; max-width: 600px;}  
        </style>
    </head>
    <body yahoo bgcolor="#fff">
        <table width="100%" bgcolor="#fff" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <table class="content" align="center" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td>
                                <p>Hallo @{{pelanggan}},</p>

                                <p>Email ini berisi pemberitahuan bahwa pembayaran order anda berhasi dilakukan.</p>
                                <br>Detail order anda <hr>KodeOrder: @{{kodeorder}}
                                <br>Tanggal Order: @{{tanggal}}
                                <br>Kode Pembayaran @{{kode_pembayaran}}
                                
                                <hr>
                                
                                <p>Order anda akan segera diproses. Jika ada pertanyaan atau keluhan bisa menghubungi kami melalui web kami.<p>
                                <br></p> Salam Hangat, @{{toko}}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
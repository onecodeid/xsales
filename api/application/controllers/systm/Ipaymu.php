<?php

class Ipaymu extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('finance/f_ipaymu');
    }

    function index()
    {
        return;
    }

    function balance_check()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->IPAYMU_URL."saldo?key=".$this->IPAYMU_KEY."&format=json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
            $d = json_decode($response);
            if ($d->Status == "200")
                $this->sys_ok($d->Saldo);
            else
                $this->sys_error('0');
        }
        
    }

    function test_get_session()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->IPAYMU_URL."direct/get-sid",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => array(
            'key' => $this->IPAYMU_KEY,
            'pay_method' => 'cstore',
            'product' => 'baju',
            'quantity' => '1',
            'price' => '10000'
            // 'ureturn' => 'https://www.ipaymu.com',
            // 'unotify' => 'https://www.ipaymu.com',
            // 'ucancel' => 'https://www.ipaymu.com/cancel'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

    function req_payment_cs()
    {
        $this->load->model('sales/l_so');
        $so = $this->l_so->get_one($this->sys_input['id']);
        if (!$so)
            die();
        
        if ($so->payment_code != 'IPAYMU.CS')
            die();

        $params = array(
                'key' => $this->IPAYMU_KEY,
                'pay_method' => 'cstore',
                'buyer_name'    => $so->M_CustomerName,
                'buyer_phone'   => $so->M_CustomerPhone,
                'buyer_email'   => 'heriabunizar@gmail.com',//$so->M_CustomerEmail,
                'ureturn' => base_url().'/systm/ipaymu/redirect?soid='.$this->sys_input['id'],
                'unotify' => base_url().'/systm/ipaymu/notify?soid='.$this->sys_input['id'],
                'ucancel' => base_url().'/systm/ipaymu/cancel?soid='.$this->sys_input['id']
        );

        $items = ['name'=>[], 'qty'=>[], 'price'=>[]];
        foreach ($so->items as $k => $v)
        {
            // $params['product['.$k.']'] = $v->item_name;
            // $params['quantity['.$k.']'] = $v->item_qty;
            // $params['price['.$k.']'] = $v->item_price;

            $params['product'] = 'Zalfa Miracle Kosmetik';
            $params['quantity'] = 1;
            $params['price'] = $so->L_SoTotal;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->IPAYMU_URL2."direct/get-sid",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $params
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $x = json_decode($response);

        $this->pay_cs([
            'so_id' => $this->sys_input['id'],
            'sessionID' => $x->sessionID,
            'channel'   => $so->L_SoPaymentChannel
        ]);
    }

    function pay_cs ($d)
    {
        $this->load->model('sales/l_so');
        $so = $this->l_so->get_one($d['so_id']);
        if (!$so)
            die();
            
        $params = array(
                'key' => $this->IPAYMU_KEY,
                'sessionID' => $d['sessionID'],
                'pay_method' => 'cstore',
                'channel' => $d['channel'],
                'name'    => $so->M_CustomerName,
                'phone'   => $so->M_CustomerPhone,
                'email'   => 'heriabunizar@gmail.com'//$so->M_CustomerEmail
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            // CURLOPT_URL => $this->IPAYMU_URL2."direct/pay/cstore",
            CURLOPT_URL => $this->IPAYMU_URL."payment/cstore",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $params,
          ));

        $response = curl_exec($curl);

        curl_close($curl);
        $x = json_decode($response);
        
        $r = $this->f_ipaymu->create($d['so_id'], [
            'session_id'    => $d['sessionID'],
            'channel'       => $d['channel'],
            'trx_id'        => $x->trx_id,
            'via'           => 'cstore',
            'code'          => $x->kode_pembayaran,
            'expired'       => date('Y-m-d H:i:s', strtotime(preg_replace('/(WIB)/', '', $x->expired))),
            'note'          => $x->keterangan
        ]);
        // print_r($r);
        // header("Location:".$x->url);
    }

    function req_payment_qris()
    {
        $this->load->model('sales/l_so');
        $so = $this->l_so->get_one($this->sys_input['id']);
        if (!$so)
            die();
        
        if ($so->payment_code != 'IPAYMU.QRIS')
            die();

        $this->pay_qris(['so_id'=>$this->sys_input['id']]);
    }
    
    function pay_qris ($d)
    {
        // $d = $this->sys_input;
        $this->load->model('sales/l_so');
        $so = $this->l_so->get_one($d['so_id']);
        if (!$so)
            die();
            
        $params = array(
                'key' => $this->IPAYMU_KEY,
                // 'sessionID' => $d['sessionID'],
                // 'pay_method' => 'cstore',
                // 'channel' => $d['channel'],
                'name'    => $so->M_CustomerName,
                'phone'   => $so->M_CustomerPhone,
                'email'   => 'heriabunizar@gmail.com',
                'amount'    => $so->L_SoTotal,
                'notifyUrl' => base_url().'/systm/ipaymu/notify?soid='.$d['so_id'],
                'referenceId'   => $so->L_SoNumber,
                'zipCode'       => '55555',
                'city'          => 'Bogor'
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            // CURLOPT_URL => $this->IPAYMU_URL2."direct/pay/cstore",
            CURLOPT_URL => $this->IPAYMU_URL."payment/qris",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $params,
          ));

        $response = curl_exec($curl);

        curl_close($curl);
        $x = json_decode($response);

        $r = $this->f_ipaymu->create($d['so_id'], [
            'session_id'    => $x->Data->SessionId,
            'channel'       => $x->Data->Channel,
            'trx_id'        => $x->Data->TransactionId,
            'via'           => $x->Data->Via,
            'code'          => $x->Data->PaymentNo,
            'expired'       => date('Y-m-d H:i:s', strtotime(preg_replace('/(WIB)/', '', $x->Data->Expired))),
            'note'          => $x->Data->PaymentName,
            'qrstring'      => $x->Data->QrString,
            'qrimage'       => $x->Data->QrImage,
            'fee'           => $x->Data->Fee
        ]);
        // print_r($r);
        // header("Location:".$x->url);
    }

    function redirect()
    {
        $x = $this->sys_input;
        $r = $this->f_ipaymu->create($x['soid'], $x);

        // echo base_url().'../ui/app/finance-ipaymu-redirect/?id='.$r->data->ipaymu_id;
        if ($r->status == "OK")
            header("Location:".base_url().'../ui/app/finance-ipaymu-redirect/?id='.$x['soid']);
    }

    function notify()
    {
        $r = $this->f_ipaymu->notify( $this->sys_input );
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function cancel()
    {
        $r = $this->f_ipaymu->cancel( $this->sys_input );
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function notify_qris()
    {
        return $this->notify();
    }

    function qris ($soid)
    {
        $this->load->library('ciqrcode');
        $this->load->model('finance/f_ipaymu');
        $ipaymu = $this->f_ipaymu->get($soid);

        header("Content-Type: image/png");
        $params['data'] = $ipaymu->F_IpaymuQrString;
        $params['level'] = 'H';
        $params['size'] = 3;

        $this->ciqrcode->generate($params);
    }
}

?>
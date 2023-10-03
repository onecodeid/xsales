<?php

class Payment extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('finance/f_payment');
        $this->load->model('finance/f_payment2');
    }

    function search()
    {
        $inp = $this->sys_input;
        $r = $this->f_payment->search(['search'=>'%'.$inp['search'].'%', 'page'=>$inp['page'], 'sdate'=>$inp['sdate'], 'edate'=>$inp['edate']]);
        $this->sys_ok($r);
    }

    function save()
    {
        $hdata = (array) json_decode($this->sys_input['hdata']);
        $hdata['uid'] = $this->sys_user['user_id'];
        $this->sys_input['hdata'] = json_encode($hdata);
        
        // chck double data
        $jdata = json_decode($this->sys_input['jdata']);
        $details = [];
        $double = false;
        foreach ($jdata as $k => $v)
        {
            if (!isset($v->is_retur))
                $v->is_retur = "N";
            if (array_search($v->invoice.$v->disc.$v->is_retur, $details) === false)
                $details[] = $v->invoice.$v->disc.$v->is_retur;
            else
                $double = true;
        }

        if ($double)
        {
            $this->sys_error("Double Invoice, please re-check your data !");
            return;
        }

        if (isset($this->sys_input['payment_id']))
            $r = $this->f_payment->save2( $this->sys_input, $this->sys_input['payment_id'] );
        else
            $r = $this->f_payment->save2( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function save_old()
    {
        $hdata = (array) json_decode($this->sys_input['hdata']);
        $hdata['uid'] = $this->sys_user['user_id'];
        $this->sys_input['hdata'] = json_encode($hdata);
        
        if (isset($this->sys_input['payment_id']))
            $r = $this->f_payment->save( $this->sys_input, $this->sys_input['payment_id'] );
        else
            $r = $this->f_payment->save( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function del()
    {
        $r = $this->f_payment->del( $this->sys_input['id'] );
        if ($r->status == "OK")
            $this->sys_ok($r);
        else
            $this->sys_error($r);
    }

    function post()
    {
        $r = $this->f_payment->post( $this->sys_input['id'] );
        if ($r->status == "OK")
            $this->sys_ok($r);
        else
            $this->sys_error($r);
    }

    function get()
    {
        $r = $this->f_payment2->get( $this->sys_input['id'], (isset($this->sys_input['jid'])?$this->sys_input['jid']:0) );
        $this->sys_ok($r);
    }
}

?>
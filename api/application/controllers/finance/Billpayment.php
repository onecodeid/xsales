<?php

class Billpayment extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('finance/f_billpayment');
    }

    function search()
    {
        $inp = $this->sys_input;
        $r = $this->f_billpayment->search(['search'=>'%'.$inp['search'].'%', 'page'=>$inp['page'], 'sdate'=>$inp['sdate'], 'edate'=>$inp['edate']]);
        $this->sys_ok($r);
    }

    function save()
    {
        $hdata = (array) json_decode($this->sys_input['hdata']);
        $hdata['uid'] = $this->sys_user['user_id'];
        $this->sys_input['hdata'] = json_encode($hdata);
        
        if (isset($this->sys_input['payment_id']))
            $r = $this->f_billpayment->save( $this->sys_input, $this->sys_input['payment_id'] );
        else
            $r = $this->f_billpayment->save( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    // function del()
    // {
    //     $r = $this->f_billpayment->del( $this->sys_input['id'] );
    //     if ($r->status == "OK")
    //         $this->sys_ok($r);
    //     else
    //         $this->sys_error($r);
    // }

    // function post()
    // {
    //     $r = $this->f_billpayment->post( $this->sys_input['id'] );
    //     if ($r->status == "OK")
    //         $this->sys_ok($r);
    //     else
    //         $this->sys_error($r);
    // }

    function get()
    {
        $r = $this->f_billpayment->get( $this->sys_input['id'], (isset($this->sys_input['jid'])?$this->sys_input['jid']:0) );
        $this->sys_ok($r);
    }
}

?>
<?php

class Paymentdp extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('finance/f_paymentdp');
    }

    function search()
    {
        $prm = ['search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'customer_id'=>isset($this->sys_input['customer'])?$this->sys_input['customer']:0,
            'account'=>isset($this->sys_input['account'])?$this->sys_input['account']:''];
        if (isset($this->sys_input['edits']))
            $prm['edits'] = $this->sys_input['edits'];
        if (isset($this->sys_input['full']))
            $prm['full'] = $this->sys_input['full'];

        $r = $this->f_paymentdp->search($prm);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'vendor_id'=>$this->sys_input['vendor_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $r = $this->f_paymentdp->search_autocomplete($x);
        $this->sys_ok($r);
    }

    function save()
    {
        if (isset($this->sys_input['dp_id']))
            $r = $this->f_paymentdp->save( $this->sys_input, $this->sys_input['dp_id'], $this->sys_user['user_id'] );
        else
            $r = $this->f_paymentdp->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function delete()
    {
        if (isset($this->sys_input['dp_id']))
            $r = $this->f_paymentdp->delete( $this->sys_input['dp_id'], $this->sys_user['user_id'] );
        else {
            $this->sys_error("System Error");
            return;
        }
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }
}

?>
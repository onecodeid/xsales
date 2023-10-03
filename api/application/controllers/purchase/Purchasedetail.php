<?php

class Purchasedetail extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('purchase/p_purchasedetail');
    }

    // function search()
    // {
    //     $r = $this->p_purchase->search(['search'=>$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']]);
    //     $this->sys_ok($r);
    // }

    function search_dd()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'purchase_id'=>isset($this->sys_input['purchase_id'])?$this->sys_input['purchase_id']:0,
                'vendor_id'=>isset($this->sys_input['vendor_id'])?$this->sys_input['vendor_id']:0,
                'purchase_id'=>isset($this->sys_input['purchase_id'])?$this->sys_input['purchase_id']:0];
                
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $x['page'] = 1;
        $x['limit'] = 9999999;
        $r = $this->p_purchasedetail->search_dd($x);
        $this->sys_ok($r);
    }

    function save()
    {
        if (isset($this->sys_input['purchase_id']))
            $r = $this->p_purchase->save( $this->sys_input, $this->sys_input['payment_id'], $this->sys_user['user_id'] );
        else
            $r = $this->p_purchase->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }
}

?>
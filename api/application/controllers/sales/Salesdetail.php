<?php

class Salesdetail extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('sales/l_salesdetail');
    }

    // function search()
    // {
    //     $r = $this->l_sales->search(['search'=>$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']]);
    //     $this->sys_ok($r);
    // }

    function search_dd()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'sales_id'=>isset($this->sys_input['sales_id'])?$this->sys_input['sales_id']:0,
                'customer_id'=>isset($this->sys_input['customer_id'])?$this->sys_input['customer_id']:0];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $x['page'] = 1;
        $x['limit'] = 9999999;
        $r = $this->l_salesdetail->search_dd($x);
        $this->sys_ok($r);
    }

    function save()
    {
        if (isset($this->sys_input['sales_id']))
            $r = $this->l_sales->save( $this->sys_input, $this->sys_input['payment_id'], $this->sys_user['user_id'] );
        else
            $r = $this->l_sales->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function save_item_name()
    {
        $r = $this->l_salesdetail->save_item_name( $this->sys_input['id'], $this->sys_input['itemname'] );
        
        $this->sys_ok($r);
    }
}

?>
<?php

class Retur extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('sales/l_retur');
    }

    function save()
    {
        if (isset($this->sys_input['retur_id']))
            $r = $this->l_retur->save( $this->sys_input, $this->sys_input['retur_id'], $this->sys_user['user_id'] );
        else
            $r = $this->l_retur->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok(json_decode($r->data));
        else
            $this->sys_error($r->message);
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'customer_id'=>isset($this->sys_input['customer_id'])?$this->sys_input['customer_id']:0
        ];
        $r = $this->l_retur->search($prm);
        $this->sys_ok($r);
    }

    function search_id()
    {
        $r = $this->l_retur->search_id($this->sys_input['retur_id']);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $this->sys_ok([]);
        return;
        $x = ['search'=>$this->sys_input['search'].'%', 
                'customer_id'=>$this->sys_input['customer_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $r = $this->l_retur->search_autocomplete($x);
        $this->sys_ok($r);
    }
}

?>
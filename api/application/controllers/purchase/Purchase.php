<?php

class Purchase extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('purchase/p_purchase');
    }

    function search()
    {
        $p = ['search'=>'%'.$this->sys_input['search'].'%', 
                'page'=>$this->sys_input['page'],
                'sdate'=>$this->sys_input['sdate'],
                'edate'=>$this->sys_input['edate']];

        if (isset($this->sys_input['done']))
            $p['done'] = $this->sys_input['done'];

        $r = $this->p_purchase->search($p);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'supplier_id'=>$this->sys_input['vendor_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $r = $this->p_purchase->search_autocomplete($x);
        $this->sys_ok($r);
    }

    function save()
    {
        if (isset($this->sys_input['purchase_id']))
            $r = $this->p_purchase->save( $this->sys_input, $this->sys_input['purchase_id'], $this->sys_user['user_id'] );
        else
            $r = $this->p_purchase->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function del()
    {
        if (isset($this->sys_input['id']))
        {
            $r = $this->p_purchase->delete( $this->sys_input['id'] );
            if ($r->status == "OK")
                $this->sys_ok($r->data);
            else
                $this->sys_error($r->message);
        }
        else
            $this->sys_error("Something wrong");
    }
}

?>
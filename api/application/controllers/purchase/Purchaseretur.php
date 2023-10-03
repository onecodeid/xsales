<?php

class Purchaseretur extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('purchase/p_purchaseretur');
    }

    function search()
    {
        $r = $this->p_purchaseretur->search(['search'=>$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']]);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'supplier_id'=>$this->sys_input['vendor_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $r = $this->p_purchaseretur->search_autocomplete($x);
        $this->sys_ok($r);
    }
}

?>
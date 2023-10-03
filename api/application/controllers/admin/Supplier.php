<?php

class Supplier extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('admin/a_supplier');
        $this->load->model('master/m_vendor');
    }

    function search()
    {
        $r = $this->m_vendor->search(['search'=>'%'.$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']]);
        // $r = $this->a_supplier->search(['search'=>'%'.$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']]);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $r = $this->m_vendor->search_autocomplete(['vendor_name'=>'%'.$this->sys_input['search'].'%','province'=>0,'city'=>0]);
        // $r = $this->a_supplier->search_autocomplete(['search'=>'%'.$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }
}

?>
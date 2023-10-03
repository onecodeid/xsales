<?php

class Customer extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('admin/a_customer');
    }

    function search()
    {
        $r = $this->a_customer->search(['search'=>'%'.$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']]);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $r = $this->a_customer->search_autocomplete(['search'=>'%'.$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }
}

?>
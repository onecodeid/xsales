<?php

class Paymentdetail extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_paymentdetail');
    }

    function search()
    {
        $r = $this->m_paymentdetail->search(['search'=>'%'.$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']]);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $r = $this->m_paymentdetail->search_autocomplete(['search'=>'%'.$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }
}

?>
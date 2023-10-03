<?php

class Bank extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_bank');
    }

    function search()
    {
        $d = ['search'=>'%'.$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']];
        if (isset($this->sys_input['limit']))
            $d['limit'] = $this->sys_input['limit'];
        $r = $this->m_bank->search($d);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $r = $this->m_bank->search_autocomplete(['search'=>'%'.$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }
}

?>
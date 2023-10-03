<?php

class Term extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_term');
    }

    function search()
    {
        $r = $this->m_term->search(['search'=>$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']]);
        $this->sys_ok($r);
    }

    function search_dd()
    {
        $r = $this->m_term->search(['search'=>'%', 'limit'=>99999, 'page'=>1]);
        $this->sys_ok($r);
    }
}

?>
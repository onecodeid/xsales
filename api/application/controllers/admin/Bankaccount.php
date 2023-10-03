<?php

class Bankaccount extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('admin/a_bankaccount');
    }

    function search()
    {
        $r = $this->a_bankaccount->search(['search'=>'%'.$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']]);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $r = $this->a_bankaccount->search_autocomplete(['search'=>'%'.$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }
}

?>
<?php

class Customerregion extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('admin/a_customerregion');
    }

    function search()
    {
        $r = $this->a_customerregion->search(['search'=>'%'.$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']]);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $r = $this->a_customerregion->search_autocomplete(['search'=>'%'.$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }
}

?>
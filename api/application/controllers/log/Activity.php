<?php

class Activity extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('log/log_activity');
    }

    function search()
    {
        $r = $this->l_offer->search(['search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate']]);
        $this->sys_ok($r);
    }
}

?>
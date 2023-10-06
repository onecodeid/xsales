<?php

class Customerdisc extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_customerdisc');
    }

    function get()
    {
        $r = $this->m_customerdisc->get($this->sys_input['customer_id'], $this->sys_input['disc_id']);

        if ($r)
            $this->sys_ok($r);
        else
            $this->sys_error('ERROR');
    }
}
?>
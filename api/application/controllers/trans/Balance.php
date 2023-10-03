<?php

class Balance extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('trans/t_balance');
    }

    function set()
    {
        $r = $this->t_balance->set( $this->sys_input );

        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }
}

?>
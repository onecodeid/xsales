<?php

class Inventory extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('analytic/an_inventory');
    }

    function pareto()
    {
        $r = $this->an_inventory->pareto(
            date("Y-m-d", strtotime($this->sys_input['sdate'])), 
            date("Y-m-d", strtotime($this->sys_input['edate'])),
            isset($this->sys_input['warehouse'])?$this->sys_input['warehouse']:0,
            isset($this->sys_input['orderby'])?$this->sys_input['orderby']:'omzet_freq desc');

        $this->sys_ok($r);
    }

    function recapt_daily()
    {
        $r = $this->an_inventory->recapt_daily(
            date("Y-m-d", strtotime($this->sys_input['sdate'])),
            date("Y-m-d", strtotime($this->sys_input['edate'])),
            isset($this->sys_input['warehouse'])?$this->sys_input['warehouse']:0);

        $this->sys_ok($r);
    }

    function omzet_category()
    {
        $r = $this->an_inventory->omzet_category(
            date("Y-m-d", strtotime($this->sys_input['sdate'])),
            date("Y-m-d", strtotime($this->sys_input['edate'])),
            isset($this->sys_input['category'])?$this->sys_input['category']:0);

        $this->sys_ok($r);
    }
}

?>
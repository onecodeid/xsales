<?php

class Dashboard2 extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('dashboard/d_dashboard2');
    }

    function Sales001()
    {
        $r = $this->d_dashboard2->sales001($this->sys_user['user_id'], $this->sys_input['sdate'], $this->sys_input['edate']);
        $this->sys_ok($r);
    }

    function Finance002()
    {
        $r = $this->d_dashboard2->finance002(isset($this->sys_input['sdate'])?$this->sys_input['sdate']:date('Y-m-01'),
            isset($this->sys_input['edate'])?$this->sys_input['edate']:date('Y-m-d'));
        // $r = $this->d_dashboard2->finance002(isset($this->sys_input['period'])?$this->sys_input['period']:'M');
        $this->sys_ok($r);
    }

    function Finance003()
    {
        $r = $this->d_dashboard2->finance003(isset($this->sys_input['sdate'])?$this->sys_input['sdate']:date('Y-m-01'),
            isset($this->sys_input['edate'])?$this->sys_input['edate']:date('Y-m-d'));
        // $r = $this->d_dashboard2->finance003(isset($this->sys_input['period'])?$this->sys_input['period']:'M');
        $this->sys_ok($r);
    }

    function Finance004()
    {
        $r = $this->d_dashboard2->finance004(isset($this->sys_input['sdate'])?$this->sys_input['sdate']:date('Y-m-01'),
            isset($this->sys_input['edate'])?$this->sys_input['edate']:date('Y-m-d'));
        // $r = $this->d_dashboard2->finance004(isset($this->sys_input['period'])?$this->sys_input['period']:'Y');
        $this->sys_ok($r);
    }

    function Ratio_liquidity()
    {
        $r = $this->d_dashboard2->ratio_liquidity($this->sys_input['sdate'], $this->sys_input['edate']);
        $this->sys_ok($r);
    }

    function Ratio_activity()
    {
        $r = $this->d_dashboard2->ratio_activity($this->sys_input['sdate'], $this->sys_input['edate']);
        $this->sys_ok($r);
    }

    function Margin_profitability()
    {
        $r = $this->d_dashboard2->margin_profitability($this->sys_input['sdate'], $this->sys_input['edate']);
        $this->sys_ok($r);
    }
}
?>
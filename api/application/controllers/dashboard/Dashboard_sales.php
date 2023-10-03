<?php

class Dashboard_sales extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('dashboard/d_dashboardsales');
    }

    function Sales_customer_001()
    {
        // $this->sys_input['sdate'] = "2022-08-01";
        // $this->sys_input['edate'] = "2022-08-31";
        $r = $this->d_dashboardsales->sales_customer_001($this->sys_input['sdate'], $this->sys_input['edate'], 
                        isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0);

        $this->sys_ok($r);
    }

    function Sales_002()
    {
        // $this->sys_input['sdate'] = "2022-08-01";
        // $this->sys_input['edate'] = "2022-08-31";
        $r = $this->d_dashboardsales->sales_002($this->sys_input['sdate'], $this->sys_input['edate'], 
                        isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0);

        $this->sys_ok($r);
    }

    function Sales_003()
    {
        // $this->sys_input['sdate'] = "2022-08-01";
        // $this->sys_input['edate'] = "2022-08-31";
        $r = $this->d_dashboardsales->sales_003($this->sys_input['sdate'], $this->sys_input['edate'], 
                        isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0);

        $this->sys_ok($r);
    }

    // function Finance002()
    // {
    //     $r = $this->d_dashboard2->finance002(isset($this->sys_input['period'])?$this->sys_input['period']:'M');
    //     $this->sys_ok($r);
    // }

    // function Finance003()
    // {
    //     $r = $this->d_dashboard2->finance003(isset($this->sys_input['period'])?$this->sys_input['period']:'M');
    //     $this->sys_ok($r);
    // }

    // function Finance004()
    // {
    //     $r = $this->d_dashboard2->finance004(isset($this->sys_input['period'])?$this->sys_input['period']:'Y');
    //     $this->sys_ok($r);
    // }

    // function Ratio_liquidity()
    // {
    //     $r = $this->d_dashboard2->ratio_liquidity($this->sys_input['sdate'], $this->sys_input['edate']);
    //     $this->sys_ok($r);
    // }

    // function Margin_profitability()
    // {
    //     $r = $this->d_dashboard2->margin_profitability($this->sys_input['sdate'], $this->sys_input['edate']);
    //     $this->sys_ok($r);
    // }
}
?>
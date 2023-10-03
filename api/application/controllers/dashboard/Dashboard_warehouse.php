<?php

class Dashboard_warehouse extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('dashboard/d_dashboardwarehouse');
    }

    function Warehouse001()
    {
        // $this->sys_input['sdate'] = "2022-08-01";
        // $this->sys_input['edate'] = "2022-10-31";
        $r = $this->d_dashboardwarehouse->warehouse001($this->sys_input['sdate'], $this->sys_input['edate'], 
                        isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0);

        $this->sys_ok($r);
    }

    function Warehouse002()
    {
        // $this->sys_input['sdate'] = "2022-08-01";
        // $this->sys_input['edate'] = "2022-10-31";
        $r = $this->d_dashboardwarehouse->warehouse002($this->sys_input['sdate'], $this->sys_input['edate'], 
                        isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0);

        $this->sys_ok($r);
    }

    function Warehouse003()
    {
        $r = $this->d_dashboardwarehouse->warehouse003($this->sys_input['sdate'], $this->sys_input['edate']);
        $this->sys_ok($r);
    }
}
?>
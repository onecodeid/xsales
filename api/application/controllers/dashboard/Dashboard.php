<?php

class Dashboard extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('dashboard/d_dashboard');
    }

    function get_customer_total_by_admin()
    {
        $r = $this->d_dashboard->get_customer_total_by_admin($this->sys_user['user_id']);
        $this->sys_ok($r);
    }

    function get_fee_total_by_admin()
    {
        $sdate = date('Y-m-01');
        $edate = date('Y-m-d');

        $r = $this->d_dashboard->get_fee_total_by_admin($this->sys_user['user_id'], $sdate, $edate);
        $this->sys_ok(json_decode($r->data));
    }

    function get_omzet_total_by_product()
    {
        $sdate = date('Y-m-d', strtotime($this->sys_input['sdate']));
        $edate = date('Y-m-d', strtotime($this->sys_input['edate']));
        $type = isset($this->sys_input['type'])?$this->sys_input['type']:'A';

        $r = $this->d_dashboard->get_omzet_by_product($this->sys_user['user_id'], $sdate, $edate, $type);
        $this->sys_ok(json_decode($r->data));
    }

    function get_target_this_week()
    {
        $r = $this->d_dashboard->get_target_this_week(0);
        $data = json_decode($r->data);
        $data->total_per_days = json_decode($data->total_per_days);
        $this->sys_ok($data);
    }
    
    function get_customer_total_new()
    {
        $r = $this->d_dashboard->get_customer_total_new($this->sys_user['user_id']);
        $this->sys_ok(json_decode($r->data));
    }

    function get_omzet_admin()
    {
        $r = $this->d_dashboard->get_omzet_admin($this->sys_user['user_id']);
        $data = json_decode($r->data);
        $this->sys_ok($data);
    }
}
?>
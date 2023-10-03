<?php

class Report extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('report/r_report');
    }

    function search_groups()
    {
        $r = $this->r_report->search_groups();
        $this->sys_ok($r);
    }

    function search_group_id()
    {
        $r = $this->r_report->search_group_id($this->sys_user['group_id']);
        $this->sys_ok($r);
    }

    // PARAMS
    function search_admins()
    {
        $this->load->model('system/s_user');
        $p = $this->s_user->get_profile($this->sys_user['user_id']);
        $d['search'] = '%';
        if ($p->group_code != 'Z.GROUP.01' && $p->group_code != 'Z.GROUP.02')
        {
            $d['uid'] = $this->sys_user['user_id'];
        }
        
        $s = $this->s_user->search($d);
        $this->sys_ok($s);
    }

    function search_month()
    {
        $inp = $this->sys_input;
        $year = isset($inp['year'])?$inp['year']:date('Y');
        $month_min = 1;
        $month_max = date('n');
        
        $d = [];
        for ($i=$month_min;$i<=$month_max;$i++)
        {
            $date = $year.'-'.$i.'-01';
            $d[] = ['month_value'=>$i, 'month_name'=>date('F', strtotime($date)), 'date_last'=>date('t', strtotime($date))];
        }

        $this->sys_ok($d);
    }

    function search_months()
    {
        $r = $this->r_report->search_months();
        $this->sys_ok($r);
    }

    function search_end_years()
    {
        $r = $this->r_report->search_end_years();
        $this->sys_ok($r);
    }

    function search_years()
    {
        $years = [];
        for ($i = $this->START_YEAR; $i<= (date('Y')+1); $i++) $years[] = ['year_value'=>$i, 'year_name'=>$i];

        $this->sys_ok($years);
    }

    function search_months_in_year()
    {
        $year = $this->sys_input['year'];
        $months = [];
        for ($i=1; $i<=12; $i++)
        {
            $j = str_pad($i, 2, "0", STR_PAD_LEFT);
            $strtime = strtotime("{$year}-{$j}-01");
            $months[] = ['sdate'=>date('Y-m-01', $strtime), 'edate'=>date('Y-m-t', $strtime), 'label'=>date('M Y', $strtime)];
        }

        $this->sys_ok($months);
    }
}

?>
<?php

class Report2 extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('report/r_report2');
    }

    function search_groups()
    {
        $r = $this->r_report->search_groups();
        $this->sys_ok($r);
    }

    // PARAMS
    // function search_admins()
    // {
    //     $this->load->model('system/s_user');
    //     $p = $this->s_user->get_profile($this->sys_user['user_id']);
    //     $d['search'] = '%';
    //     if ($p->group_code != 'Z.GROUP.01' && $p->group_code != 'Z.GROUP.02')
    //     {
    //         $d['uid'] = $this->sys_user['user_id'];
    //     }
        
    //     $s = $this->s_user->search($d);
    //     $this->sys_ok($s);
    // }
}

?>
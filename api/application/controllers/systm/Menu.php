<?php

class Menu extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('system/s_menu');
    }

    function index()
    {
        return;
    }

    function search_group()
    {
        $r = $this->s_menu->search_group($this->sys_user['group_id']);

        $this->sys_ok($r);
    }

    function search_all_id()
    {
        $r = $this->s_menu->search_all_id();

        $this->sys_ok($r);
    }
}

?>
<?php

class Notif extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('system/s_notif');
    }

    function index()
    {
        return;
    }

    function get_unread()
    {
        $r = $this->s_notif->get_unread($this->sys_user['user_id']);
        // if ($r->unread > 0)
            $r->md5 = md5(json_encode($r->messages));
        // else
        //     $r->md5 = "";

        $this->sys_ok($r);
    }

    function set_read()
    {
        $r = $this->s_notif->set_read($this->sys_user['user_id']);
        $this->sys_ok($r);
    }
}

?>

<?php

class Opname extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('inventory/i_opname');
    }

    function index()
    {
        return;
    }

    function search()
    {
        $r = $this->i_opname->search(
            ['search'=>'%'.$this->sys_input['search'].'%',
            'sdate'=>date('Y-m-d 00:00:00', strtotime($this->sys_input['sdate'])),
            'edate'=>date('Y-m-d 23:59:59', strtotime($this->sys_input['edate']))]);
        $this->sys_ok($r);
    }

    function save()
    {
        $this->sys_input['uid'] = $this->sys_user['user_id'];
        $r = $this->i_opname->save( $this->sys_input );

        if ($r->status == "OK")
            $this->sys_ok($r);
        else
            $this->sys_error($r);
    }
}

?>
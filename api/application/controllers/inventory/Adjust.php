
<?php

class Adjust extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('inventory/i_adjust');
    }

    function index()
    {
        return;
    }

    function search_id()
    {
        $r = $this->i_adjust->search(
            ['search'=>'%', 'sdate'=>'2022-01-01', 'edate'=>date('Y-m-d'), 'warehouse'=>0,
            'adjust_id'=>$this->sys_input['adjust_id']]);
        if (isset($r['records'][0]))
            $this->sys_ok($r['records'][0]);
        else
            $this->sys_error('No Data');
    }

    function search()
    {
        $r = $this->i_adjust->search(
            ['search'=>'%'.$this->sys_input['search'].'%',
            'sdate'=>date('Y-m-d 00:00:00', strtotime($this->sys_input['sdate'])),
            'edate'=>date('Y-m-d 23:59:59', strtotime($this->sys_input['edate'])),
            'warehouse'=>isset($this->sys_input['warehouse'])?$this->sys_input['warehouse']:0]);
        $this->sys_ok($r);
    }

    function save()
    {
        $this->sys_input['uid'] = $this->sys_user['user_id'];
        $r = $this->i_adjust->save( $this->sys_input );

        if ($r->status == "OK")
            $this->sys_ok($r);
        else
            $this->sys_error($r);
    }

    // function del()
    // {
    //     $r = $this->m_customer->del( $this->sys_input );
    //     $this->sys_ok($r);
    // }
}

?>

<?php

class Transfer extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('inventory/i_transfer');
    }

    function index()
    {
        return;
    }

    function search_id()
    {
        $r = $this->i_transfer->search(
            ['search'=>'%', 'sdate'=>'2023-01-01', 'edate'=>date('Y-m-d'), 'from'=>0, 'to'=>0,
            'transfer_id'=>$this->sys_input['transfer_id']]);
        if (isset($r['records'][0]))
            $this->sys_ok($r['records'][0]);
        else
            $this->sys_error('No Data');
    }

    function search()
    {
        $r = $this->i_transfer->search(
            ['search'=>'%'.$this->sys_input['search'].'%',
            'sdate'=>date('Y-m-d 00:00:00', strtotime($this->sys_input['sdate'])),
            'edate'=>date('Y-m-d 23:59:59', strtotime($this->sys_input['edate'])),
            'from'=>(isset($this->sys_input['from'])?$this->sys_input['from']:0),
            'to'=>(isset($this->sys_input['to'])?$this->sys_input['to']:0)]);
        $this->sys_ok($r);
    }

    function save()
    {
        $this->sys_input['uid'] = $this->sys_user['user_id'];
        $r = $this->i_transfer->save( $this->sys_input );

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
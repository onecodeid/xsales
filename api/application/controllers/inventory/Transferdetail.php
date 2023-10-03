
<?php

class Transferdetail extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('inventory/i_transferdetail');
    }

    function index()
    {
        return;
    }

    function search()
    {
        $r = $this->i_transferdetail->search(['transfer_id'=>$this->sys_input['transfer_id'], 'search'=>'%']);
        $this->sys_ok($r);
    }

    // function save()
    // {
    //     $r = $this->m_customer->save( $this->sys_input );
    //     echo json_encode($r);
    // }

    // function del()
    // {
    //     $r = $this->m_customer->del( $this->sys_input );
    //     $this->sys_ok($r);
    // }
}

?>
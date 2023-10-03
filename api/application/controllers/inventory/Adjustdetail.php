
<?php

class Adjustdetail extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('inventory/i_adjustdetail');
    }

    function index()
    {
        return;
    }

    function search()
    {
        $r = $this->i_adjustdetail->search(['adjust_id'=>$this->sys_input['adjust_id'], 'search'=>'%']);
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
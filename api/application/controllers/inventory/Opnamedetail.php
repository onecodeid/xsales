
<?php

class Opnamedetail extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('inventory/i_opnamedetail');
    }

    function index()
    {
        return;
    }

    function search()
    {
        $r = $this->i_opnamedetail->search(['opname_id'=>$this->sys_input['opname_id'], 'item_name'=>'%','page'=>1]);
        $this->sys_ok($r);
    }

    function search_item()
    {
        $r = $this->i_opnamedetail->search_item(['item_name'=>'%','page'=>1]);
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
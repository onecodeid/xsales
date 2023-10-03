
<?php

class Assemblydetail extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('inventory/i_assemblydetail');
    }

    function index()
    {
        return;
    }

    function search()
    {
        $r = $this->i_assemblydetail->search(['assembly_id'=>$this->sys_input['assembly_id'], 'search'=>'%']);
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
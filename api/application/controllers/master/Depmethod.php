<?php

class Depmethod extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_depmethod');
    }

    function search()
    {
        $r = $this->m_depmethod->search(['depmethod_name'=>$this->sys_input['search'].'%', 'page'=>$this->sys_input['page']]);
        $this->sys_ok($r);
    }

    function search_dd()
    {
        $r = $this->m_depmethod->search_dd(['depmethod_name'=>$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }

    function save()
    {
        $this->sys_input['user_id'] = $this->sys_user['user_id'];
        if (isset($this->sys_input['depmethod_id']))
            $r = $this->m_depmethod->save( $this->sys_input, $this->sys_input['depmethod_id'] );
        else
            $r = $this->m_depmethod->save( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error('ERROR');
    }

    function del()
    {
        $r = $this->m_depmethod->del( $this->sys_input );
        $this->sys_ok($r);
    }
}

?>
<?php

class Pack extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_pack');
    }

    function search()
    {
        $r = $this->m_pack->search(['search'=>'%'.$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }

    function save()
    {
        $this->sys_input['user_id'] = $this->sys_user['user_id'];
        if (isset($this->sys_input['pack_id']))
            $r = $this->m_pack->save( $this->sys_input, $this->sys_input['pack_id'] );
        else
            $r = $this->m_pack->save( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error('ERROR');
    }

    function del()
    {
        $r = $this->m_pack->del( $this->sys_input );
        $this->sys_ok($r);
    }
}

?>
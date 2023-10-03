<?php

class Affiliate extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_affiliate');
    }

    function search()
    {
        $r = $this->m_affiliate->search(['search'=>'%'.$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }

    function save()
    {
        $this->sys_input['user_id'] = $this->sys_user['user_id'];
        if (isset($this->sys_input['affiliate_id']))
            $r = $this->m_affiliate->save( $this->sys_input, $this->sys_input['affiliate_id'] );
        else
            $r = $this->m_affiliate->save( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error('ERROR');
    }

    function del()
    {
        $r = $this->m_affiliate->del( $this->sys_input );
        $this->sys_ok($r);
    }
}

?>
<?php

class Paymentplan extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_paymentplan');
    }

    function search()
    {
        $r = $this->m_paymentplan->search([
            'search'=>'%'.$this->sys_input['search'].'%',
            'page'=>$this->sys_input['page']]);
        $this->sys_ok($r);
    }

    function save()
    {
        $this->sys_input['user_id'] = $this->sys_user['user_id'];
        if (isset($this->sys_input['paymentplan_id']))
            $r = $this->m_paymentplan->save( $this->sys_input, $this->sys_input['paymentplan_id'] );
        else
            $r = $this->m_paymentplan->save( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error('ERROR');
    }

    function del()
    {
        $r = $this->m_paymentplan->del( $this->sys_input );
        $this->sys_ok($r);
    }
}

?>
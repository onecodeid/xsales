<?php

class Account extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_account');
    }

    function search()
    {
        $r = $this->m_account->search(
            ['account_name'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'group_id'=>isset($this->sys_input['group_id'])?$this->sys_input['group_id']:0,
            'side'=>isset($this->sys_input['side'])?$this->sys_input['side']:'',
            'limit'=>isset($this->sys_input['limit'])?$this->sys_input['limit']:10,
            'level'=>isset($this->sys_input['level'])?$this->sys_input['level']:0]);
        $this->sys_ok($r);
    }

    function search_dd()
    {
        $r = $this->m_account->search_dd(['account_name'=>$this->sys_input['search'].'%',
                'group_id'=>isset($this->sys_input['group_id'])?$this->sys_input['group_id']:0]);
        $this->sys_ok($r);
    }

    function search_for_parent()
    {
        $r = $this->m_account->search(
            ['account_name'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'group_id'=>isset($this->sys_input['group_id'])?$this->sys_input['group_id']:0,
            'side'=>isset($this->sys_input['side'])?$this->sys_input['side']:'',
            'limit'=>isset($this->sys_input['limit'])?$this->sys_input['limit']:10,
            'level'=>isset($this->sys_input['level'])?$this->sys_input['level']:0,
            'addon_where'=>"AND LENGTH(a.M_AccountCode) = 7 AND (a.M_AccountParent = 'Y' OR (a.M_AccountParent = 'N' AND a.M_AccountUsed = 'N'))"]);
        $this->sys_ok($r);
    }

    function save()
    {
        $this->sys_input['user_id'] = $this->sys_user['user_id'];
        if (isset($this->sys_input['account_id']))
            $r = $this->m_account->save( $this->sys_input, $this->sys_input['account_id'] );
        else
            $r = $this->m_account->save( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error('ERROR');
    }

    function save_new()
    {
        $this->sys_input['user_id'] = $this->sys_user['user_id'];
        if (isset($this->sys_input['account_id']))
            $r = $this->m_account->save_new( $this->sys_input, $this->sys_input['account_id'] );
        else
            $r = $this->m_account->save_new( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error('ERROR');
    }

    function del()
    {
        $r = $this->m_account->del( $this->sys_input['id'], $this->sys_user['user_id'] );

        $this->sys_output($r);
    }
}

?>
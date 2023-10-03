<?php

class Item extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_item');
    }

    function search()
    {
        $r = $this->m_item->search([
            'item_name'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'warehouse'=>isset($this->sys_input['warehouse'])?$this->sys_input['warehouse']:0,
            'assembly'=>isset($this->sys_input['assembly'])?$this->sys_input['assembly']:'']
            );
        $this->sys_ok($r);
    }

    function search_dd()
    {
        $r = $this->m_item->search([
            'item_name'=>'%', 
            'limit'=>99999, 
            'page'=>1,
            'assembly'=>isset($this->sys_input['assembly'])?$this->sys_input['assembly']:'']);
        $this->sys_ok($r);
    }

    function save()
    {
        $this->sys_input['user_id'] = $this->sys_user['user_id'];
        if (isset($this->sys_input['item_id']))
            $r = $this->m_item->save( $this->sys_input, $this->sys_input['item_id'] );
        else
            $r = $this->m_item->save( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function del()
    {
        $r = $this->m_item->del( $this->sys_input );
        $this->sys_ok($r);
    }

    function set_slow()
    {
        $r = $this->m_item->set_slow( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }
}

?>
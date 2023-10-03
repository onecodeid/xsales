<?php

class Category extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_category');
    }

    function search()
    {
        $r = $this->m_category->search(['search'=>'%'.(isset($this->sys_input['search'])?$this->sys_input['search']:'').'%']);
        $this->sys_ok($r);
    }

    function search_dd()
    {
        $r = $this->m_category->search([
            'search'=>'%', 
            'limit'=>99999, 
            'page'=>1]);
        $this->sys_ok($r);
    }

    function save()
    {
        $this->sys_input['user_id'] = $this->sys_user['user_id'];
        if (isset($this->sys_input['category_id']))
            $r = $this->m_category->save( $this->sys_input, $this->sys_input['category_id'] );
        else
            $r = $this->m_category->save( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error('ERROR');
    }

    function del()
    {
        $r = $this->m_category->del( $this->sys_input );
        $this->sys_ok($r);
    }
}

?>
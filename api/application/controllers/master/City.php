<?php

class City extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_city');
    }

    function index()
    {
        return;
    }

    function search_full()
    {
        $r = $this->m_city->search_full(['search'=>'%'.$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }

    function search_full_kecamatan_reverse()
    {
        $r = $this->m_city->search_full_kecamatan_reverse(['search'=>'%'.$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }

    function search_full_reverse()
    {
        $r = $this->m_city->search_full_reverse(['search'=>'%'.$this->sys_input['search'].'%']);
        $this->sys_ok($r);
    }

    function search()
    {
        $r = $this->m_city->search(['city_name'=>'%', 'province_id'=>$this->sys_input['province_id']]);
        $this->sys_ok($r);
    }
}

?>
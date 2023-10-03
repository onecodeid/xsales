<?php

class Deliveryaddress extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('master/m_deliveryaddress');
    }

    function search()
    {
        $r = $this->m_deliveryaddress->search(['customer'=>$this->sys_input['customer']?$this->sys_input['customer']:0,
            'search'=>'%'.$this->sys_input['search'].'%',
            'page'=>$this->sys_input['page'] ]);

        // phone format
        foreach ($r['records'] as $k => $v)
        {
            $phones = [];
            $phone = [];
            foreach ($v['address_phone'] as $l => $w)
            {
                $w->no = $this->phone_format($w->no);
                $phones[] = $w;
                $phone[] = $w->no;
                // $v['address_phone'][$l]->no = $this->phone_format($w->no);
            }
            $v['address_phones'] = $phones;
            $v['address_phone'] = join(", ", $phone);
            $r['records'][$k] = $v;
        }

        $this->sys_ok($r);
    }

    function search_dd()
    {
        $r = $this->m_customer->search([
            'customer_name'=>'%', 
            'limit'=>99999, 
            'page'=>1, 'city'=>0, 'province'=>0, 'type'=>'']);
        $this->sys_ok($r);
    }

    function save()
    {
        $this->sys_input['user_id'] = $this->sys_user['user_id'];
        if (isset($this->sys_input['address_id']))
            $r = $this->m_deliveryaddress->edit( $this->sys_input );
        else
            $r = $this->m_deliveryaddress->save( $this->sys_input );

        echo json_encode($r);
    }

    function del()
    {
        $r = $this->m_customer->del( $this->sys_input['customer_id'], $this->sys_user['user_id'] );
        if ($r->status=='OK')
            $this->sys_ok(json_decode($r->data));
        else
            $this->sys_error($r->message);
    }

    function search_autocomplete()
    {
        $r = $this->m_customer->search_autocomplete(
            ['customer_name'=>'%'.$this->sys_input['customer_name'].'%', 
            'page'=>1,
            'city'=>0,
            'province'=>0,
            'type'=>'']);
        $this->sys_ok($r);
    }

    function set_index()
    {
        $this->m_customer->set_index( isset($this->sys_input['id']) ? $this->sys_input['id'] : 0 );
    }

    function search_similar()
    {
        $r = $this->m_customer->search_similar($this->sys_input['search']);
        $this->sys_ok($r);
    }

    function tmp_idx()
    {
        $this->m_customer->_tmp_idx();
    }
}

?>
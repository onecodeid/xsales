<?php

class Offer extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('sales/l_offer');
    }

    function search()
    {
        $r = $this->l_offer->search(['search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'lead'=>isset($this->sys_input['lead'])?$this->sys_input['lead']:0,
            'staff_id'=>isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0,
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'staff_id'=>isset($this->sys_input['staff'])?$this->sys_input['staff']:0]);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $x = ['search'=>'%', 
                'customer_id'=>$this->sys_input['customer_id'],
                'staff_id'=>isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0,
                'used'=>isset($this->sys_input['used'])?$this->sys_input['used']:''];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $r = $this->l_offer->search_autocomplete($x);
        $this->sys_ok($r);
    }

    function save()
    {
        if (isset($this->sys_input['offer_id']))
            $r = $this->l_offer->save( $this->sys_input, $this->sys_input['offer_id'], $this->sys_user['user_id'] );
        else
            $r = $this->l_offer->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function del()
    {
        if (isset($this->sys_input['id']))
        {
            $r = $this->l_offer->delete( $this->sys_input['id'] );
            if ($r->status == "OK")
                $this->sys_ok($r->data);
            else
                $this->sys_error($r->message);
        }
        else
            $this->sys_error("Something wrong");
    }

    function get()
    {
        $r = $this->l_offer->get($this->sys_input['id']);

        if ($r['records'])
            $this->sys_ok($r['records'][0]);
        else
            $this->sys_error('Not Found');
    }

    function convert_to_sales()
    {
        if (isset($this->sys_input['offer_id']))
            $r = $this->l_offer->convert_to_sales( $this->sys_input['offer_id'] );
        else
        {
            $this->sys_error( "ID Error !" );
            return;
        }
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function get_for_duplicate()
    {
        $r = $this->l_offer->get_for_duplicate($this->sys_input['id'], isset($this->sys_input['from'])?$this->sys_input['from']:'OFFER');
        $this->sys_ok($r);
    }
}

?>
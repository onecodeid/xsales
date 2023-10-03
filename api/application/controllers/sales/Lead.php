<?php

class Lead extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('sales/l_lead');
    }

    function search()
    {
        $r = $this->l_lead->search(['search'=>$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'staff_id'=>isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0]);
        $this->sys_ok($r);
    }

    // function search_autocomplete()
    // {
    //     $x = ['search'=>'%', 
    //             'customer_id'=>$this->sys_input['customer_id'],
    //             'staff_id'=>isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0,
    //             'used'=>isset($this->sys_input['used'])?$this->sys_input['used']:''];
    //     if (isset($this->sys_input['edits']))
    //         $x['edits'] = $this->sys_input['edits'];
            
    //     $r = $this->l_lead->search_autocomplete($x);
    //     $this->sys_ok($r);
    // }

    function save()
    {
        if (isset($this->sys_input['lead_id']))
            $r = $this->l_lead->save( $this->sys_input, $this->sys_input['lead_id'], $this->sys_user['user_id'] );
        else
            $r = $this->l_lead->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function del()
    {
        if (isset($this->sys_input['id']))
        {
            $r = $this->l_lead->delete( $this->sys_input['id'] );
            if ($r->status == "OK")
                $this->sys_ok($r->data);
            else
                $this->sys_error($r->message);
        }
        else
            $this->sys_error("Something wrong");
    }
}

?>
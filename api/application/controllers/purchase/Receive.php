<?php

class Receive extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('purchase/p_receive');
    }

    function search_id()
    {
        $id = $this->sys_input['receive_id'];
        if (strpos($id, 'd') !== false)
            $id = $this->p_receive->get_parent_id(str_replace('d', '', $id));

        $r = $this->p_receive->search(['search'=>'%', 'page'=>1, 'sdate'=>'2023-01-01', 'edate'=>date('Y-m-d'),
                        'receive_id'=>$id]);
        if (isset($r['records'][0]))
            $this->sys_ok($r['records'][0]);
        else
            $this->sys_error('No Data');
    }

    function search()
    {
        $r = $this->p_receive->search(['search'=>'%'.$this->sys_input['search'].'%', 
                        'page'=>$this->sys_input['page'],
                        'sdate'=>$this->sys_input['sdate'],
                        'edate'=>$this->sys_input['edate']]);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'supplier_id'=>$this->sys_input['supplier_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $r = $this->p_receive->search_autocomplete($x);
        $this->sys_ok($r);
    }

    function search_dd()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'vendor_id'=>$this->sys_input['vendor_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $r = $this->p_receive->search_dd($x);
        $this->sys_ok($r);
    }

    function save()
    {
        if (isset($this->sys_input['receive_id']))
            $r = $this->p_receive->save( $this->sys_input, $this->sys_input['receive_id'], $this->sys_user['user_id'] );
        else
            $r = $this->p_receive->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function delete()
    {
        if (isset($this->sys_input['receive_id']))
            $r = $this->p_receive->delete( $this->sys_input['receive_id'], $this->sys_user['user_id'] );
        else {
            $this->sys_error("System Error :(");
            return;
        }
            
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function confirm()
    {
        if (isset($this->sys_input['receive_id']))
            $r = $this->p_receive->confirm( $this->sys_input['receive_id'], $this->sys_user['user_id'] );
        else {
            $this->sys_error("System Error :(");
            return;
        }
            
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }
}

?>
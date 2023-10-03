<?php

class Bill extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('finance/f_bill');
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'sdate'=>isset($this->sys_input['sdate'])?$this->sys_input['sdate']:"2021-01-01",
            'edate'=>isset($this->sys_input['edate'])?$this->sys_input['edate']:date("Y-m-d"),
            'duedate'=>isset($this->sys_input['duedate'])?$this->sys_input['duedate']:"2029-01-01"];
        if (isset($this->sys_input['lunas'])) $prm['lunas'] = $this->sys_input['lunas'];

        $r = $this->f_bill->search($prm);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'vendor_id'=>$this->sys_input['vendor_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $r = $this->f_bill->search_autocomplete($x);
        $this->sys_ok($r);
    }

    function save()
    {
        $hdata = json_decode($this->sys_input['hdata']);
        $hdata->dps = isset($this->sys_input['dps'])?json_decode($this->sys_input['dps']):[];
        $hdata->date = date("Y-m-d", strtotime($hdata->date));
        $this->sys_input['hdata'] = json_encode($hdata);

        if (isset($this->sys_input['bill_id']))
            $r = $this->f_bill->save( $this->sys_input, $this->sys_input['bill_id'], $this->sys_user['user_id'] );
        else
            $r = $this->f_bill->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function save_create()
    {
        $hdata = json_decode($this->sys_input['hdata']);
        $hdata->dps = isset($this->sys_input['dps'])?json_decode($this->sys_input['dps']):[];
        $this->sys_input['hdata'] = json_encode($hdata);

        if (isset($this->sys_input['bill_id']))
            $r = $this->f_bill->save_create( $this->sys_input, $this->sys_input['bill_id'], $this->sys_user['user_id'] );
        else
            $r = $this->f_bill->save_create( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok(json_decode($r->data));
        else
            $this->sys_error($r->message);
    }

    function delete()
    {
        if (isset($this->sys_input['bill_id']))
            $r = $this->f_bill->delete( $this->sys_input['bill_id'], $this->sys_user['user_id'] );
        else {
            $this->sys_error("System Error");
            return;
        }

        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function header_stats()
    {
        $r = $this->f_bill->header_stats();

        $this->sys_ok($r);
    }
}

?>
<?php

class Sales extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('sales/l_sales');
    }

    function search()
    {
        $r = $this->l_sales->search(['search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'staff_id'=>isset($this->sys_input['staff'])?$this->sys_input['staff']:0,
            'customer_id'=>isset($this->sys_input['customer'])?$this->sys_input['customer']:0]);
        $this->sys_ok($r);
    }

    function search_month()
    {
        $sdate = date('Y-m-01', strtotime($this->sys_input['cal_date']));
        $edate = date('Y-m-t', strtotime($this->sys_input['cal_date']));

        $r = $this->l_sales->search(['search'=>'%'.$this->sys_input['search'].'%', 
                        'page'=>1,
                        'sdate'=>$sdate,
                        'edate'=>$edate,
                        'limit'=>1000]);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'customer_id'=>$this->sys_input['customer_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $r = $this->l_sales->search_autocomplete($x);
        $this->sys_ok($r);
    }

    function save()
    {
        if (isset($this->sys_input['sales_id']))
            $r = $this->l_sales->save( $this->sys_input, $this->sys_input['sales_id'], $this->sys_user['user_id'] );
        else
            $r = $this->l_sales->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function save_proforma()
    {
        $hdata = json_decode($this->sys_input['hdata']);
        $hdata->due_date = date('Y-m-d', strtotime($hdata->due_date));
        // $hdata->dps = json_decode($this->sys_input['dps']);
        $hdata->proforma = "Y";
        $this->sys_input['hdata'] = json_encode($hdata);

        $r = $this->l_sales->save_proforma( $this->sys_input['sales_id'], $this->sys_input, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
        {
            // $x = json_decode($r->data);
            // $y = $this->l_sales->get_by_journal($x->journal_id);
            // $x->invoice_id = $y->L_InvoiceID;
            $this->sys_ok(json_decode($r->data));
        }
            
        else
            $this->sys_error($r->message);
    }

    function del()
    {
        if (isset($this->sys_input['id']))
        {
            $r = $this->l_sales->delete( $this->sys_input['id'] );
            if ($r->status == "OK")
                $this->sys_ok($r->data);
            else
                $this->sys_error($r->message);
        }
        else
            $this->sys_error("Something wrong");
    }

    function search_4_proforma()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'customer_id'=>$this->sys_input['customer_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
        if (isset($this->sys_input['used']))
            $x['used'] = $this->sys_input['used'];
        if (isset($this->sys_input['invoice_only']))
            $x['invoice_only'] = $this->sys_input['invoice_only'];

        if (isset($this->sys_input['sales_id']))                
            $x['sales_id'] = $this->sys_input['sales_id'];
            
        $r = $this->l_sales->search_4_proforma($x);
        $this->sys_ok($r['records']);
    }

    function search_id()
    {
        $r = $this->l_sales->search_id($this->sys_input['sales_id']);
        $this->sys_ok($r);
    }
}

?>
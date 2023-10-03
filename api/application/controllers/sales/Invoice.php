<?php

class Invoice extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('sales/l_invoice');
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'sdate'=>isset($this->sys_input['sdate'])?$this->sys_input['sdate']:"2021-01-01",
            'edate'=>isset($this->sys_input['edate'])?$this->sys_input['edate']:date("Y-m-d"),
            'duedate'=>isset($this->sys_input['duedate'])?$this->sys_input['duedate']:"2029-01-01",
            'customer_id'=>isset($this->sys_input['customer_id'])?$this->sys_input['customer_id']:0,
            'staff_id'=>isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0
        ];
        if (isset($this->sys_input['lunas'])) $prm['lunas'] = $this->sys_input['lunas'];
        if (isset($this->sys_input['retur'])) $prm['retur'] = $this->sys_input['retur'];

        $r = $this->l_invoice->search($prm);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'customer_id'=>$this->sys_input['customer_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $r = $this->l_invoice->search_autocomplete($x);
        $this->sys_ok($r);
    }

    function save()
    {
        $hdata = json_decode($this->sys_input['hdata']);
        $hdata->due_date = date('Y-m-d', strtotime($hdata->due_date));
        $hdata->date = date('Y-m-d', strtotime($hdata->date));
        $hdata->dps = json_decode($this->sys_input['dps']);
        $this->sys_input['hdata'] = json_encode($hdata);
        
        if (isset($this->sys_input['invoice_id']))
            $r = $this->l_invoice->save( $this->sys_input, $this->sys_input['invoice_id'], $this->sys_user['user_id'] );
        else
            $r = $this->l_invoice->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function save_proforma()
    {
        $hdata = json_decode($this->sys_input['hdata']);
        $hdata->due_date = date('Y-m-d', strtotime($hdata->due_date));
        $hdata->dps = json_decode($this->sys_input['dps']);
        $hdata->proforma = "Y";
        $this->sys_input['hdata'] = json_encode($hdata);
        
        if (isset($this->sys_input['invoice_id']))
            $r = $this->l_invoice->save_proforma( $this->sys_input, $this->sys_input['invoice_id'], $this->sys_user['user_id'] );
        else
            $r = $this->l_invoice->save_proforma( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
        {
            $x = json_decode($r->data);
            $y = $this->l_invoice->get_by_journal($x->journal_id);
            $x->invoice_id = $y->L_InvoiceID;
            $this->sys_ok($x);
        }
            
        else
            $this->sys_error($r->message);
    }

    function delete()
    {
        if (isset($this->sys_input['invoice_id']))
            $r = $this->l_invoice->delete( $this->sys_input['invoice_id'], $this->sys_user['user_id'] );
        else {
            $this->sys_error("System Error");
            return;
        }
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function search_id()
    {
        $r = $this->l_invoice->search_id($this->sys_input['invoice_id']);
        $this->sys_ok($r);
    }

    function header_stats()
    {
        $r = $this->l_invoice->header_stats();

        $this->sys_ok($r);
    }
}

?>
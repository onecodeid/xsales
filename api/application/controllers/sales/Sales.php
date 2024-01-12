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
        $prm = ['search'=>'%'.$this->sys_input['search'].'%', 
                'page'=>$this->sys_input['page'],
                'sdate'=>$this->sys_input['sdate'],
                'edate'=>$this->sys_input['edate'],
                'customer_id'=>isset($this->sys_input['customer_id'])?$this->sys_input['customer_id']:0,
                'staff_id'=>isset($this->sys_input['staff'])?$this->sys_input['staff']:0,
                'customer_id'=>isset($this->sys_input['customer'])?$this->sys_input['customer']:0];
        
        if (isset($this->sys_input['retur'])) $prm['retur'] = $this->sys_input['retur'];

        $r = $this->l_sales->search($prm);
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

    function search_available_by_item()
    {
        $prm = ['search'=>'%'.$this->sys_input['search'].'%', 
                'page'=>$this->sys_input['page'],
                'item_id'=>$this->sys_input['item_id'],
                'customer_id'=>isset($this->sys_input['customer_id'])?$this->sys_input['customer_id']:0];

        $r = $this->l_sales->search_available_by_item($prm);
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

    function invoice()
    {
        $cols = ["no"=>["w"=>5,"t"=>"NO"],
            "name"=>["w"=>30,"t"=>"NAMA","f"=>"TOTAL"], 
            "price"=>["w"=>15,"a"=>STR_PAD_LEFT,"t"=>"HARGA"], 
            "qty"=>["w"=>10,"a"=>STR_PAD_LEFT,"t"=>"QTY"], 
            "bruto"=>["w"=>15,"a"=>STR_PAD_LEFT,"t"=>"BRUTO"], 
            "disc"=>["w"=>10,"a"=>STR_PAD_LEFT,"t"=>"DISKON"], 
            "netto"=>["w"=>15,"a"=>STR_PAD_LEFT,"t"=>"NETTO"]];
        $data = [
            ["no" => 1, "name" => "Silica Gel Curah", "price" => 3400, "qty" => 50, "bruto" => 170000, "disc" => 1000, "netto" => 169000],
            ["no" => 2, "name" => "Dummy Product 1", "price" => 2500, "qty" => 30, "bruto" => 75000, "disc" => 500, "netto" => 74500],
            ["no" => 3, "name" => "Dummy Product 2", "price" => 1800, "qty" => 20, "bruto" => 36000, "disc" => 1200, "netto" => 34800],
            ["no" => 4, "name" => "Dummy Product 3", "price" => 4200, "qty" => 40, "bruto" => 168000, "disc" => 2000, "netto" => 166000],
            ["no" => 5, "name" => "Dummy Product 4", "price" => 3100, "qty" => 25, "bruto" => 77500, "disc" => 750, "netto" => 76750],
            ["no" => 6, "name" => "Dummy Product 5", "price" => 2700, "qty" => 35, "bruto" => 94500, "disc" => 800, "netto" => 93700],
        ];
        
        $data_lines = [];
        $titles = [];
        $footer = [];
        $width = 0;
        foreach ($cols as $k => $v) {
            $titles[] = str_pad($v['t'], $v["w"], " ", isset($v["a"])?$v["a"]:STR_PAD_RIGHT);
            $width += $v['w'];
        }
        
        $bruto=0; $disc=0; $netto=0;
        foreach ($data as $k => $v) {
            $x = [];
            foreach ($v as $l => $w) {
                $x[] = str_pad(is_numeric($w)?number_format($w):$w, $cols[$l]["w"], " ", isset($cols[$l]["a"])?$cols[$l]["a"]:STR_PAD_RIGHT);
                if ($l=='bruto') $bruto+=$w;
                if ($l=='netto') $netto+=$w;
                if ($l=='disc') $disc+=$w;
            }
            $data_lines[] = join("", $x);
        }
        
        $cols["bruto"]["f"] = number_format($bruto);
        $cols["disc"]["f"] = number_format($disc);
        $cols["netto"]["f"] = number_format($netto);
        foreach ($cols as $k => $v)
            $footer[] = str_pad(isset($v['f'])?$v['f']:' ', $v["w"], " ", isset($v["a"])?$v["a"]:STR_PAD_RIGHT);
          
        $x = [str_repeat("=", $width)];
        $x[] = join("", $titles);
        $x[] = str_repeat("-", $width);
        $x = array_merge($x, $data_lines);
        $x[] = str_repeat("-", $width);
        $x[] = join("", $footer);

        echo join(chr(10), $x);
    }
}

?>
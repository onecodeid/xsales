<?php

class Delivery extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('sales/l_delivery');
    }

    function search_id()
    {
        $id = $this->sys_input['delivery_id'];
        if (strpos($id, 'd') !== false)
            $id = $this->l_delivery->get_parent_id(str_replace('d', '', $id));

        $r = $this->l_delivery->search(['search'=>'%', 'page'=>1, 'sdate'=>'2023-01-01', 'edate'=>date('Y-m-d'),
                                    'delivery_id'=>$id]);
        if (isset($r['records'][0]))
            $this->sys_ok($r['records'][0]);
        else
            $this->sys_error('No Data');
    }

    function search()
    {
        $r = $this->l_delivery->search(['search'=>'%'.$this->sys_input['search'].'%', 
                        'page'=>$this->sys_input['page'],
                        'sdate'=>$this->sys_input['sdate'],
                        'edate'=>$this->sys_input['edate']]);
        $this->sys_ok($r);
    }

    function search_month()
    {
        $sdate = date('Y-m-01', strtotime($this->sys_input['cal_date']));
        $edate = date('Y-m-t', strtotime($this->sys_input['cal_date']));

        $r = $this->l_delivery->search(['search'=>'%'.$this->sys_input['search'].'%', 
                        'page'=>1,
                        'sdate'=>$sdate,
                        'edate'=>$edate]);
        $this->sys_ok($r);
    }

    function search_autocomplete()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'supplier_id'=>$this->sys_input['supplier_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
            
        $r = $this->l_delivery->search_autocomplete($x);
        $this->sys_ok($r);
    }

    function search_dd()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'customer_id'=>$this->sys_input['customer_id']];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
        if (isset($this->sys_input['used']))
            $x['used'] = $this->sys_input['used'];
        if (isset($this->sys_input['invoice_only']))
            $x['invoice_only'] = $this->sys_input['invoice_only'];

        if (isset($this->sys_input['delivery_id']))
            $x['delivery_id'] = $this->sys_input['delivery_id'];
            
        $r = $this->l_delivery->search_dd($x);
        foreach ($r as $k => $v)
            if (isset($v['dp'])) $r[$k]['dp'] = json_decode($v['dp']);
        $this->sys_ok($r);
    }

    function search_proforma()
    {
        $x = ['search'=>$this->sys_input['search'].'%', 
                'page'=>isset($this->sys_input['page'])?$this->sys_input['page']:1,
                'customer_id'=>isset($this->sys_input['customer_id'])?$this->sys_input['customer_id']:0];
        if (isset($this->sys_input['edits']))
            $x['edits'] = $this->sys_input['edits'];
        if (isset($this->sys_input['used']))
            $x['used'] = $this->sys_input['used'];
        if (isset($this->sys_input['invoice_only']))
            $x['invoice_only'] = $this->sys_input['invoice_only'];
            
        $r = $this->l_delivery->search_proforma($x);
        $this->sys_ok($r);
    }

    function save()
    {
        if (!isset($this->sys_input['draft']))
        {
            $items = [];
            $wh = json_decode($this->sys_input['hdata']);
            $wh = $wh->p_warehouse;
            // $wh = (json_decode($this->sys_input['hdata']))->p_warehouse;
            $jdata = json_decode($this->sys_input['jdata']);
            foreach ($jdata as $k => $v)
                $items[] = $v->itemid;

            $x = $this->check_stock($items, $wh, $jdata);

            if (!$x)
            {
                $this->sys_error('Ada Item yang tidak mencukupi stoknya, mohon cek kembali');
                return;
            }
        }

        // if ($this->sys_input['proforma']=='Y')
        //     $r = $this->l_delivery->save_proforma( $this->sys_input, $this->sys_input['delivery_id'], $this->sys_user['user_id'] );
        // else 
        if (isset($this->sys_input['delivery_id']))
            $r = $this->l_delivery->save( $this->sys_input, $this->sys_input['delivery_id'], $this->sys_user['user_id'] );
        else
            $r = $this->l_delivery->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function check_stock($items, $wh, $details)
    {
        $r = true;
        $this->load->model('inventory/i_stock');
        $stocks = $this->i_stock->search_by_items(join(',', $items), $wh);

        foreach ($details as $k => $v)
        {
            foreach ($stocks as $l => $w)
            {
                if ($v->itemid == $w['item_id'])
                    if ($v->qty > $w['stock_qty'])
                        $r = false;
            }
        }

        return $r;
    }

    function delete()
    {
        if (isset($this->sys_input['delivery_id']))
            $r = $this->l_delivery->delete( $this->sys_input['delivery_id'], $this->sys_user['user_id'] );
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
        if (isset($this->sys_input['delivery_id']))
            $r = $this->l_delivery->confirm( $this->sys_input['delivery_id'], $this->sys_user['user_id'] );
        else {
            $this->sys_error("System Error :(");
            return;
        }
            
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function invoice_create()
    {
        if (isset($this->sys_input['invoice_id']))
            $r = $this->l_delivery->invoice_create( $this->sys_input, $this->sys_input['invoice_id'], $this->sys_user['user_id'] );
        else
            $r = $this->l_delivery->invoice_create( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function save_item_name()
    {
        $r = $this->l_delivery->save_item_name( $this->sys_input['id'], $this->sys_input['itemname'] );
        
        $this->sys_ok($r);
    }
}

?>
<?php

class D_dashboardsales extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "r_report";
        $this->table_key = "R_ReportID";
    }

    // Statistik Omzet (qty) per product
    function Sales_customer_001($sdate, $edate, $staffid = 0)
    {
        $r = $this->MainSp("SALES.CUSTOMER.001", $sdate, $edate, $staffid);
        return $r;
    }

    function Sales_002($sdate, $edate, $staffid = 0)
    {
        // $sdate = "2022-08-01";
        // $edate = "2022-10-30";
        $r = $this->MainSp("SALES.002", $sdate, $edate, $staffid, 'array');
        return $r;
    }

    function Sales_003($sdate, $edate, $staffid = 0)
    {
        // $sdate = "2022-10-01";
        // $edate = "2022-10-30";
        $r = $this->MainSp("SALES.003", $sdate, $edate, $staffid, 'array');
        return $r;
    }

    function Finance002($period = 'M')
    {
        $r = $this->MainSp("FINANCE.002.".$period, date('Y-m-d'), date('Y-m-d'));
        $r->periods = json_decode($r->periods);
        $r->datas = json_decode($r->datas);
        $r->summary = json_decode($r->summary);

        $ndatas = [];
        foreach ($r->periods as $l => $w)
        {
            $data = ['period'=>$w,'total'=>0];
            foreach ($r->datas as $k => $v)
            {
                if ($v->period == $w)
                    $data['total'] = $v->total;
            }
            if ($w == $r->date_before) $data['period'] = "Sebelumnya";
            if ($w == $r->date_after) $data['period'] = "Akan datang";
            $ndatas[] = $data;
        }
        $r->datas = $ndatas;
        
        return $r;
    }

    function Finance003($period = 'M')
    {
        $r = $this->MainSp("FINANCE.003.".$period, date('Y-m-d'), date('Y-m-d'));
        $r->periods = json_decode($r->periods);
        $r->datas = json_decode($r->datas);
        $r->summary = json_decode($r->summary);

        $ndatas = [];
        foreach ($r->periods as $l => $w)
        {
            $data = ['period'=>$w,'total'=>0];
            foreach ($r->datas as $k => $v)
            {
                if ($v->period == $w)
                    $data['total'] = $v->total;
            }
            if ($w == $r->date_before) $data['period'] = "Sebelumnya";
            if ($w == $r->date_after) $data['period'] = "Akan datang";
            $ndatas[] = $data;
        }
        $r->datas = $ndatas;
        
        return $r;
    }

    function Finance004($period = 'M')
    {
        $limit = 4;
        $r = $this->MainSp("FINANCE.004.".$period, date('Y-m-d'), date('Y-m-d'), 'array');
        
        $datas = [];
        foreach ($r as $k => $v)
        {
            if ($k <= $limit) $datas[] = $v;
            else {

                $datas[$limit]['account_name'] = 'Lainnya';
                $datas[$limit]['jdebit'] += $v['jdebit'];
                $datas[$limit]['jcredit'] += $v['jcredit'];
            }
        }
        return $datas;
    }

    function Ratio_liquidity($sdate, $edate)
    {
        $r = [];
        $r['current'] = $this->MainSp("RATIO.CURRENT", $sdate, $edate);
        $r['quick'] = $this->MainSp("RATIO.QUICK", $sdate, $edate);
        $r['cash'] = $this->MainSp("RATIO.CASH", $sdate, $edate);
        
        return $r;
    }

    function Margin_profitability($sdate, $edate)
    {
        $r = $this->db->query("CALL `sp_r_ONE-FIN-003-2`(?,?)", [$sdate, $edate])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);

        $sales = 0;
        $gross = 0;
        $operational = 0;
        $nett = 0;
        foreach ($r as $k => $v)
        {
            if ($v['sub_type'] == 'INCOME.SALES') $sales = $v['sub_total'];
            if ($v['sub_type'] == 'INCOME.HPP') $gross -= $v['sub_total'];
            if ($v['sub_type'] == 'INCOME.EXPENSE') $operational -= $v['sub_total'];
            if ($v['sub_type'] == 'INCOME.OTHER') $nett += $v['sub_total'];
            if ($v['sub_type'] == 'INCOME.EXPENSE.OTHER') $nett -= $v['sub_total'];
        }
        $gross += $sales;
        $operational += $gross;
        $nett += $operational;

        $x = [
            ['id'=>0,'title'=>'Margin Laba Kotor', 'formula' => ['top'=>'Laba Kotor','bottom'=>'Pendapatan Usaha'], 'amount'=>['top'=>$gross,'bottom'=>$sales]],
            ['id'=>1,'title'=>'Margin Laba Operasional', 'formula' => ['top'=>'Laba Operasional','bottom'=>'Pendapatan Usaha'], 'amount'=>['top'=>$operational,'bottom'=>$sales]],
            ['id'=>2,'title'=>'Margin Laba Bersih', 'formula' => ['top'=>'Laba Bersih','bottom'=>'Pendapatan Usaha'], 'amount'=>['top'=>$nett,'bottom'=>$sales]]
        ];

        return $x;
    }

    function MainSp($section, $sdate, $edate, $staffid = 0, $dataset = 'row')
    {
        $r = $this->db->query("CALL sp_dashboard_sales(?, ?, ?, ?)", [$section, $sdate, $edate, $staffid]);
        $r = $dataset == 'row' ? $r->row() : $r->result_array();

        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }
}
?>
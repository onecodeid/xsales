<?php

class D_dashboard2 extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "r_report";
        $this->table_key = "R_ReportID";
    }

    // Statistik Omzet (qty) per product
    function Sales001($uid, $sdate, $edate)
    {
        $r = $this->MainSp("SALES.001", $sdate, $edate);
        $r->omzets = json_decode($r->omzets);
        return $r;
    }

    function Finance002($sdate, $edate)
    {
        $r = $this->MainSp("FINANCE.002.D", date('Y-m-d', strtotime($sdate)), date('Y-m-d', strtotime($edate)));
        // $r = $this->MainSp("FINANCE.002.".$period, date('Y-m-d'), date('Y-m-d'));
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
            // if ($w == $r->date_before) $data['period'] = "Sebelumnya";
            // if ($w == $r->date_after) $data['period'] = "Akan datang";
            $ndatas[] = $data;
        }
        $r->datas = $ndatas;
        
        return $r;
    }

    function Finance003($sdate, $edate)
    {
        $r = $this->MainSp("FINANCE.003.D", date('Y-m-d', strtotime($sdate)), date('Y-m-d', strtotime($edate)));
        // $r = $this->MainSp("FINANCE.003.".$period, date('Y-m-d'), date('Y-m-d'));
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
            // if ($w == $r->date_before) $data['period'] = "Sebelumnya";
            // if ($w == $r->date_after) $data['period'] = "Akan datang";
            $ndatas[] = $data;
        }
        $r->datas = $ndatas;
        
        return $r;
    }

    function Finance004($sdate, $edate)
    {
        $limit = 4;
        $r = $this->MainSp("FINANCE.004.D", date('Y-m-d', strtotime($sdate)), date('Y-m-d', strtotime($edate)), 'array');
        // $r = $this->MainSp("FINANCE.004.".$period, date('Y-m-d'), date('Y-m-d'), 'array');
        
        $datas = [];
        $hpps = ['account_name'=>'','jcredit'=>0,'jdebit'=>0];
        foreach ($r as $k => $v)
        {
            if ($k <= $limit) $datas[] = $v;
            else if ($v['group_id'] == 15) {
                $hpps['account_name'] = 'Beban Pokok Pendapatan';
                $hpps['jdebit'] += $v['jdebit'];
                $hpps['jcredit'] += $v['jcredit'];
            }
            else {
                $datas[$limit]['account_name'] = 'Lainnya';
                $datas[$limit]['jdebit'] += $v['jdebit'];
                $datas[$limit]['jcredit'] += $v['jcredit'];
            }
        }
        if ($hpps['jdebit']>0||$hpps['jcredit']>0) array_unshift($datas, $hpps);
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

    function Ratio_activity($sdate, $edate)
    {
        $r = [];
        $r['receivable'] = $this->MainSp("RATIO.ACT.RECEIVABLE", $sdate, $edate);
        $r['inventory'] = $this->MainSp("RATIO.ACT.INVENTORY", $sdate, $edate);
        $r['fixed'] = $this->MainSp("RATIO.ACT.FIXED", $sdate, $edate);
        $r['total'] = $this->MainSp("RATIO.ACT.ACTIVA.TOTAL", $sdate, $edate);
        $r['current'] = $this->MainSp("RATIO.ACT.CAPITAL", $sdate, $edate);

        $coll = json_decode(json_encode($r['receivable']));
        $coll->rst = ((($coll->down + $coll->bdown) / 2) * 365) / $coll->top;
        $coll->unit = 'hr';
        $r['collection'] = $coll;
        
        return $r;
    }

    function Margin_profitability($sdate, $edate)
    {
        $r = $this->db->query("CALL `sp_r_ONE-FIN-003-2d`(?,?)", [$sdate, $edate])
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

    function MainSp($section, $sdate, $edate, $dataset = 'row')
    {
        $r = $this->db->query("CALL sp_dashboard(?, ?, ?)", [$section, $sdate, $edate]);
        $r = $dataset == 'row' ? $r->row() : $r->result_array();

        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }
}
?>
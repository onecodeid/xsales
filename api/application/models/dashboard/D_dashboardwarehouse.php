<?php

class D_dashboardwarehouse extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "r_report";
        $this->table_key = "R_ReportID";
    }

    // Statistik Omzet (qty) per product
    function Warehouse001($sdate, $edate, $staffid = 0)
    {
        $r = $this->MainSp("WAREHOUSE.001", $sdate, $edate, $staffid, 'array');
        return $r;
    }

    function Warehouse002($sdate, $edate, $staffid = 0)
    {
        $r = $this->MainSp("WAREHOUSE.002", $sdate, $edate, $staffid, 'array');

        foreach ($r as $k => $v)
        {
            $r[$k]['data'] = [
                ['freq_name'=>'FM', 'item_qty'=>$v['fm']],
                ['freq_name'=>'MM', 'item_qty'=>$v['mm']],
                ['freq_name'=>'SM', 'item_qty'=>$v['sm']],
                ['freq_name'=>'HSM', 'item_qty'=>$v['hsm']]
            ];
        }

        return $r;
    }

    function Warehouse003($sdate, $edate)
    {
        $r = $this->MainSp("WAREHOUSE.003", $sdate, $edate);
        $r->deliveries = json_decode($r->deliveries);
        $r->deliveries2 = json_decode($r->deliveries2);
        return $r;
    }

    function MainSp($section, $sdate, $edate, $staffid = 0, $dataset = 'row')
    {
        $r = $this->db->query("CALL sp_dashboard_warehouse(?, ?, ?, ?)", [$section, $sdate, $edate, $staffid]);
        $r = $dataset == 'row' ? $r->row() : $r->result_array();

        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }
}
?>
<?php

class R_report extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "r_report";
        $this->table_key = "R_ReportID";
    }

    function search_groups()
    {
        $r = $this->db->query("SELECT a.*, CONCAT('[', GROUP_CONCAT(JSON_OBJECT('report_id', R_ReportID, 'report_name', R_ReportName, 'report_code', R_ReportCode, 'report_desc', R_ReportDesc, 'report_url', R_ReportUrl, 'report_type', R_ReportType) SEPARATOR ','), ']') childs FROM
                            (SELECT a.R_ReportID report_id, a.R_ReportName report_name, 
                            a.R_ReportLeft report_left, a.R_ReportRight report_right, a.R_ReportIcon report_icon, a.R_ReportCode report_code,
                            a.R_ReportDesc report_desc
                            FROM r_report a
                            LEFT JOIN r_report b ON b.R_ReportLeft < a.R_ReportLeft AND b.R_ReportRight > a.R_ReportRight AND b.R_ReportIsActive = 'Y'
                            WHERE a.R_ReportIsActive = 'Y'
                            GROUP BY a.R_ReportID
                            HAVING COUNT(b.R_ReportID) < 1
                            ORDER BY a.R_ReportLeft) a
                            JOIN r_report c ON report_left < R_ReportLeft AND report_right > R_ReportRight AND R_ReportIsActive = 'Y'
                            GROUP BY report_id
                            ");
        if ($r)
        {               
            $r = $r->result_array();
            foreach ($r as $k => $v)
                $r[$k]['childs'] = json_decode($v['childs']);
            return ['records'=>$r];
        }

        return ['records'=>[]];
    }

    function search_group_id($id)
    {
        $r = $this->db->query("CALL `sp_system_report_group`(?)", [$id])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);

        if ($r)
        {
            foreach ($r as $k => $v)
                $r[$k]['childs'] = json_decode($v['childs']);
        }
        
        return ['records'=>$r];
    }

    // Report Pending Invoice / Piutang Per Sales & Region Detail
    function One_vp_001($sdate, $edate, $salesid, $regionid)
    {
        $r = $this->db->query("CALL `sp_r_ONE-VP-001`(?,?,?,?)", [$salesid, $sdate, $edate, $regionid])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    // Report Piutang Per Customer
    function One_vp_002($sdate, $edate, $customerid, $all = "N")
    {
        $r = $this->db->query("CALL `sp_r_ONE-VP-002`(?,?,?,?)", [$customerid, $sdate, $edate, $all])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    // Report Nota DO
    function One_sales_001($id)
    {
        $r = $this->db->query("CALL `sp_r_ONE-SAL-001`(?)", [$id])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    // Report Nota Invoice
    function One_sales_002($id, $uid)
    {
        $r = $this->db->query("CALL `sp_r_ONE-SAL-002`(?, ?)", [$id, $uid])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    // Report Nota Invoice
    function One_sales_002_proforma($id, $uid)
    {
        $r = $this->db->query("CALL `sp_r_ONE-SAL-002-PROFORMA`(?, ?)", [$id, $uid])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    // Report Rekapitulasi Omzet per Sales
    function One_sales_003($sdate, $edate)
    {
        $r = $this->GetMultipleQueryResult("CALL `sp_r_ONE-SAL-003`('{$sdate}', '{$edate}')", 2);
        return $r;
    }

    // Report Nota Penawaran
    function One_sales_004($id, $uid)
    {
        $r = $this->db->query("CALL `sp_r_ONE-SAL-004`(?, ?)", [$id, $uid])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    // Report Rekapitulasi Lead Detail
    function One_sales_008($staffid, $sdate, $edate)
    {
        $r = $this->GetMultipleQueryResult("CALL `sp_r_ONE-SAL-008`('{$staffid}', '{$sdate}', '{$edate}')", 2);
        return $r;
    }

    // Report Rekapitulasi Lead
    function One_sales_009($sdate, $edate)
    {
        $r = $this->GetMultipleQueryResult("CALL `sp_r_ONE-SAL-009`('{$sdate}', '{$edate}')", 2);
        return $r;
    }

    // Report Penjualan Per Sales Per Tanggal Exclude PPN
    function One_sales_011($staffid, $sdate, $edate, $iscompany)
    {
        $r = $this->db->query("CALL `sp_r_ONE-SAL-011`(?, ?, ?, ?)", [$staffid, $sdate, $edate, $iscompany])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    // Report Penjualan Per Sales Per Tanggal Exclude PPN
    function One_sales_012($staffid, $sdate, $edate, $iscompany)
    {
        $r = $this->db->query("CALL `sp_r_ONE-SAL-012`(?, ?, ?, ?)", [$staffid, $sdate, $edate, $iscompany])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    // Report Penjualan Per Sales Per Tanggal Detail Item
    function One_sales_013($staffid, $sdate, $edate)
    {
        $r = $this->db->query("CALL `sp_r_ONE-SAL-013`(?, ?, ?)", [$staffid, $sdate, $edate])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    // Report Penjualan Per Detail Item / Produk Per Tanggal
    function One_sales_014($itemid, $sdate, $edate)
    {
        $r = $this->db->query("CALL `sp_r_ONE-SAL-014`(?, ?, ?)", [$itemid, $sdate, $edate])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    // Report Nota PO
    function One_purchase_001($id, $uid)
    {
        $r = $this->db->query("CALL `sp_r_ONE-PUR-001`(?, ?)", [$id, $uid])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    // Usia Hutang
    function One_purchase_002($vendor_id = 0, $term = 0)
    {
        $r = $this->db->query("CALL `sp_r_ONE-PUR-002`(?, ?)", [$vendor_id, $term])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    // Report All Stock All Warehouse
    function One_iv_001($warehouse = 0)
    {
        $r = $this->GetMultipleQueryResult("CALL `sp_r_ONE-IV-001`()", 2);
        if (!isset($r[1]))
            $r[1] = [];

        return $r;
    }

    // Report Stock Card
    function One_iv_002($item, $warehouse, $sdate, $edate)
    {
        $r = $this->GetMultipleQueryResult("CALL `sp_r_ONE-IV-002`('{$item}','{$warehouse}','{$sdate}','{$edate}')", 2);
        if (!isset($r[1]))
            $r[1] = [];

        return $r;
    }

    // Report All Stock All Warehouse
    function One_iv_003($date, $warehouse = 0)
    {
        $r = $this->GetMultipleQueryResult("CALL `sp_r_ONE-IV-003`('{$warehouse}', '{$date}')", 2);
        if (!isset($r[1]))
            $r[1] = [];

        return $r;
    }

    // Laporan Mutasi Barang per Gudang
    function One_iv_004($warehouse, $sdate, $edate)
    {
        $r = $this->db->query("CALL `sp_r_ONE-IV-004`(?, ?, ?)", [$warehouse, $sdate, $edate])
                ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);
        
        return $r;
    }

    // // Report Omzet Per Level
    // function One_sales_003($uid, $sdate, $edate, $type = 'A')
    // {
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_sales_003`('{$uid}', '{$sdate}', '{$edate}', '{$type}')", 2);
    //     return $r;
    // }

    // // Report Omzet Per Jenjang
    // function One_sales_004($sdate, $edate, $level)
    // {
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_sales_004`('{$sdate}', '{$edate}', '{$level}')", 2);
    //     return $r;
    // }

    // // Report Omzet Per Produk
    // function One_sales_005($sdate, $edate, $type = "A")
    // {
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_sales_005`('{$sdate}', '{$edate}', '{$type}')", 1);
    //     return $r;
    // }

    // // Report Omzet Per Produk
    // // Per Kategori
    // function One_sales_006($sdate, $edate)
    // {
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_sales_006`('{$sdate}', '{$edate}')", 2);
    //     if (!isset($r[0]))
    //         $r[0] = [];

    //     $ctgrs = [];
    //     foreach ($r[0] as $k => $v)
    //         if (array_search($v['category_id'], $ctgrs) === false)
    //             $ctgrs[] = $v['category_id'];
        
    //     $categories = [];
    //     foreach ($r[1] as $k => $v)
    //         if (array_search($v['category_id'], $ctgrs) !== false)
    //             $categories[] = $v;
    //     $r[1] = $categories;

    //     return $r;
    // }

    // // Report Omzet last 3 month vs target
    // // Per Customer
    // function One_sales_007($date, $level)
    // {
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_sales_007`('{$date}', '{$level}')", 2);
    //     if (!isset($r[1]))
    //         $r[1] = [];

    //     return $r;
    // }

    // // Report Invoice Penjualan
    // function One_iv_001($id)
    // {
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_iv_001`('{$id}')", 2);
    //     return $r;
    // }

    // Report Neraca Saldo
    function One_fin_001($sdate, $edate)
    {
        $r = $this->db->query("CALL `sp_r_ONE-FIN-001`(?,?)", [$sdate, $edate])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    // Report Fee / Komisi Semua Admin
    function One_fin_002($accountid, $sdate, $edate)
    {
        $r = $this->GetMultipleQueryResult("CALL `sp_r_ONE-FIN-002`('{$accountid}','{$sdate}','{$edate}')", 2);
        return $r;
    }

    // Report Laba Rugi
    function One_fin_003($sdate, $edate)
    {
        $r = $this->db->query("CALL `sp_r_ONE-FIN-003`(?,?)", [$sdate, $edate])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    // Report Laba Rugi
    function One_fin_003_2($sdate, $edate)
    {
        $r = $this->db->query("CALL `sp_r_ONE-FIN-003-2`(?,?)", [$sdate, $edate])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);

        foreach ($r as $k => $v) 
        {
            $r[$k]['details'] = json_decode($v['details']);
            if ($v['sub_total'] == null) $r[$k]['sub_total'] = 0;
        }
        return $r;
    }

    // Report NERACA
    function One_fin_005($sdate, $edate)
    {
        $r = $this->db->query("CALL `sp_r_ONE-FIN-005`(?,?)", [$sdate, $edate])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    // Usia Piutang
    function One_fin_006($customer_id = 0)
    {
        $r = $this->db->query("CALL `sp_r_ONE-FIN-006`(?)", [$customer_id])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    // Daftar Piutang
    function One_fin_007($customer_id = 0)
    {
        $r = $this->db->query("CALL `sp_r_ONE-FIN-007`(?)", [$customer_id])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    // Report NERACA
    function One_fin_008($sdate, $edate)
    {
        $r = $this->db->query("CALL `sp_r_ONE-FIN-008`(?,?)", [$sdate, $edate])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    // // Report COD Gagal / Pengeluaran COD
    // function One_fin_004($sdate, $edate, $exp_id = 0)
    // {
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_fin_004`('{$sdate}', '{$edate}', '{$exp_id}')", 1);
    //     return $r;
    // }

    // // Report Laporan Customer
    // function One_master_001($uid, $provinceid = 0, $cityid = 0, $levelid = 0)
    // {
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_master_001`('{$uid}', '{$provinceid}', '{$cityid}', '{$levelid}')", 2);
    //     return $r;
    // }

    // Laporan Mutasi Barang Per Gudang
    // function One_wh_001($warehouse)
    // {
    //     $r = $this->db->query("CALL `sp_r_wh_001`(?)", [$warehouse])
    //             ->result_array();
    //     $this->clean_mysqli_connection($this->db->conn_id);

    //     return $r;
    // }

    // // Logbook Pengiriman
    // function One_wh_002($sdate, $edate, $exp_id = 0)
    // {
    //     $sdate = date('Y-m-d', strtotime($sdate));
    //     $edate = date('Y-m-d', strtotime($edate));
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_wh_002`('{$sdate}', '{$edate}', '{$exp_id}')", 1);
    //     return $r;
    // }

    // // Rekap Logbook Pengiriman
    // function One_wh_003($sdate, $edate, $exp_id = 0)
    // {
    //     $sdate = date('Y-m-d', strtotime($sdate));
    //     $edate = date('Y-m-d', strtotime($edate));
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_wh_003`('{$sdate}', '{$edate}', '{$exp_id}')", 1);
    //     return $r;
    // }

    // // Report Stock Card
    // function One_wh_005($id, $sdate, $edate)
    // {
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_wh_005`('{$id}', '{$sdate}', '{$edate}')", 2);
    //     return $r;
    // }

    // // Report Opname
    // function One_wh_006($id)
    // {
    //     $r = $this->GetMultipleQueryResult("CALL `sp_r_wh_006`('{$id}')", 2);
    //     return $r;
    // }

    // Report Stock Barang
    function One_wh_007($category = 0)
    {
        $r = $this->db->query("CALL `sp_r_wh_007`(?)", [$category])
                    ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    function Search_end_years()
    {
        $q = "SELECT IF(year(dt) = year(now()), date(now()), concat(year(dt), '-12-31')) edate
                FROM (
                SELECT T_JournalDate dt
                FROM t_journal
                WHERE T_JournalIsActive = 'Y'
                GROUP BY year(T_JournalDate)) x";
        $r = $this->db->query($q)->result_array();

        foreach ($r as $k => $v)
        {
            $r[$k]['sdate'] = date("Y-01-01", strtotime($v['edate']));
            $r[$k]['label'] = date("d M Y", strtotime($v['edate']));
        }

        return $r;
    }

    function Search_months($limit_min = 2)
    {
        $min_date = date("Y-01-01");
        $q = "SELECT IF(year(dt) = year(now()) and month(dt) = month(now()), date(now()), last_day(dt)) edate
                FROM (
                SELECT T_JournalDate dt
                FROM t_journal
                WHERE T_JournalIsActive = 'Y' AND T_JournalDate >= ? AND T_JournalDate <= date(now())
                GROUP BY month(T_JournalDate)) x";
        $r = $this->db->query($q, [$min_date])->result_array();

        foreach ($r as $k => $v)
        {
            $r[$k]['sdate'] = date("Y-m-01", strtotime($v['edate']));
            $r[$k]['label'] = date("01 - d M Y", strtotime($v['edate']));
            $r[$k]['label2'] = date("M Y", strtotime($v['edate']));
            $r[$k]['label3'] = date("Y-m", strtotime($v['edate']));
        }

        return $r;
    }
}

?>
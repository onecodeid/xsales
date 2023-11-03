<?php

class R_reportinventory extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_bill";
        $this->table_key = "F_BillID";
        $this->load->model("master/m_vendor");
    }

    function iv_003 ($d)
    {
        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $warehouseid = isset($d['warehouse_id'])?$d['warehouse_id']:0;

        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_iv003.sql");
        $r = $this->db->query($q, [$sdate, $warehouseid, $warehouseid, $warehouseid, $warehouseid, $d['search'], $d['search']])->result_array();

        return $r;
    }

    function iv_004 ($d)
    {
        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        $warehouseid = isset($d['warehouse_id'])?$d['warehouse_id']:0;

        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_iv004.sql");
        $r = $this->db->query($q, [$warehouseid, $sdate, $edate, $d['search'], $d['search'], $d['search'], $d['search']])->result_array();

        return $r;
    }

    function iv_005 ($d)
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $warehouse = isset($d['warehouse_id'])?$d['warehouse_id']:0;

        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        
        $q = "SELECT M_ItemID item_id, M_ItemCode item_code, M_ItemName item_name, M_UnitID unit_id, M_UnitName unit_name,
        
        CONCAT('[',
        GROUP_CONCAT(
            JSON_OBJECT('stock_id', Log_StockID, 'ref_id',  Log_StockRefID, 'ref_number', Log_StockRefNumber, 'stock_date', date_format(Log_StockDate, '%d %b %Y'),
        'customer_id', IFNULL(M_CustomerID, 0), 'customer_name', IFNULL(M_CustomerName, ''),
        'vendor_id', IFNULL(M_VendorID, 0), 'vendor_name', IFNULL(M_VendorName, ''), 
        'unit_id', M_UnitID, 'unit_name', M_UnitName,
        'warehouse_id', wa.M_WarehouseID, 'warehouse_name', wa.M_WarehouseName,
        'towarehouse_id', IFNULL(wb.M_WarehouseID, 0), 'towarehouse_name', IFNULL(wb.M_WarehouseName, ''),
        'stock_before_qty', Log_StockBeforeQty, 'stock_qty', Log_StockQty, 'stock_after_qty', Log_StockAfterQty,
        'type_code', Log_TypeCode, 'type_text', Log_TypeText) ORDER BY Log_StockDate ASC, Log_StockID ASC), ']') logs,

        SUM(IF(Log_StockQty > 0, Log_StockQty, 0)) stock_in_qty,
        SUM(IF(Log_StockQty < 0, Log_StockQty, 0)) stock_out_qty,
        M_UnitID unit_id, M_UnitName unit_name,
        M_CategoryID category_id, M_CategoryName category_name,
        wa.M_WarehouseID warehouse_id, wa.M_WarehouseName warehouse_name
        
        FROM one_account_aw_log.log_stock
        JOIN m_item ON Log_StockM_ItemID = M_ItemID
        JOIN m_unit ON M_ItemM_UnitID = M_UnitID
        JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
        LEFT JOIn m_vendor ON Log_StockM_SupplierID = M_VendorID AND Log_StockM_SupplierID <> 0
        LEFT JOIN m_customer ON Log_StockM_CustomerID = M_CustomerID AND Log_StockM_CustomerID <> 0
        JOIN m_warehouse wa ON Log_StockM_WarehouseID = wa.M_WarehouseID
        LEFT JOIN m_warehouse wb ON Log_StockFromToM_WarehouseID = wb.M_WarehouseID
        LEFT JOIN one_account_aw_log.log_type ON Log_TypeCode = Log_StockCode
        WHERE Log_StockM_WarehouseID = ?
        AND Log_StockIsActive = 'Y'
        AND Log_StockDate BETWEEN ? AND ?
        
        GROUP BY M_ItemID
        ORDER BY M_ItemName ASC, Log_StockDate asc, Log_StockID asc
        LIMIT {$limit} OFFSET {$offset}
            ";
        $r = $dbiv->query(
                $q, [$warehouse, $sdate, $edate]);

        $lx['q'] = $this->db->last_query();
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $logs = json_decode($v['logs']);
                $r[$k]['stock_before_qty'] = $logs[0]->stock_before_qty;
                $r[$k]['stock_after_qty'] = $logs[sizeof($logs)-1]->stock_after_qty;

                foreach ($logs as $l => $w)
                {
                    $w = (array) $w;
                    $text = preg_replace("/\[(supplier)\]/", $w['vendor_name'], $w['type_text']);
                    $text = preg_replace("/\[(customer)\]/", $w['customer_name'], $text);
                    $text = preg_replace("/\[(warehouse)\]/", $w['warehouse_name'], $text);
                    $text = preg_replace("/\[(towarehouse)\]/", $w['towarehouse_name'], $text);

                    $logs[$l]->type_text = $text;
                    // $text = preg_replace("/\[(warehouse)\]/", $v['warehouse_name'], $text);
                    // $text = preg_replace("/\[(towarehouse)\]/", $v['towarehouse_name'], $text);

                //     $details = $this->db->query("SELECT fn_finance_bill_detail(?) x", [$w['bill_id']])->row();
                //     $accs = $this->db->query("SELECT fn_journal_detail(?) y", [$w['journal_id']])->row();
                //     $dps = $this->db->query("SELECT fn_bill_dp(?) x", [$w['bill_id']])->row();
                    
                //     $bills[$l]->details = json_decode($details->x);
                //     $bills[$l]->accounts = json_decode($accs->y);
                //     $bills[$l]->bill_dps = json_decode($dps->x);
                  
                }

                $r[$k]['logs'] = $logs;   
            }
            $lx['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(DISTINCT `Log_StockM_ItemID`) n
            
            FROM one_account_aw_log.log_stock
            JOIN m_item ON Log_StockM_ItemID = M_ItemID
            JOIN m_unit ON M_ItemM_UnitID = M_UnitID
            JOIN m_warehouse wa ON Log_StockM_WarehouseID = wa.M_WarehouseID
            WHERE Log_StockM_WarehouseID = ?
            AND Log_StockIsActive = 'Y'
            AND Log_StockDate BETWEEN ? AND ?", [$warehouse, $sdate, $edate]);
        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $lx;
    }    

    // Report Ringkasan Persediaan
    function iv_006 ($d)
    {
        $category = isset($d['category_id'])?$d['category_id']:0;
        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2023-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        $limit = isset($d['limit']) ? $d['limit'] : 999999;
        $offset = ($d['page'] - 1) * $limit;
        
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $q = "SELECT M_ItemID item_id, M_ItemCode item_code, M_ItemName item_name,
                log_b4_qty, log_qty, log_a4_qty, M_UnitName unit_name,
                M_ItemDefaultHPP item_hpp, M_CategoryName category_name
            FROM m_item
            JOIN m_unit ON M_ItemM_Unitid = M_UnitID
            JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
            LEFT JOIN (
                SELECT Log_StockM_ItemID log_item_id, SUM(Log_StockBeforeQty) log_b4_qty,
                    SUM(Log_StockQty) log_qty, SUM(Log_StockAfterQty) log_a4_qty
                FROM xsales_log.log_stock 
                WHERE Log_StockIndex IN (
                    SELECT MAX(Log_StockIndex) idx
                    FROM xsales_log.log_stock
                    WHERE Log_StockIsActive = 'Y'
                    AND Log_StockDate <= ?
                    GROUP BY Log_StockM_ItemID, Log_StockM_WarehouseID)
                GROUP BY Log_StockM_ItemID
                ) x ON M_ItemID = log_item_id
            WHERE (log_qty IS NOT NULL OR Log_a4_qty Is NOT NULL)
            AND M_ItemIsActive = 'Y'
            AND (M_ItemName LIKE ? OR M_ItemCode LIKE ?)
            AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)
            AND log_a4_qty > 0
            ORDER BY M_ItemName ASC
            LIMIT {$limit} OFFSET {$offset}";
        
        $r = $this->db->query(
            $q, [$edate, $d['search'], $d['search'], $category, $category, $category]);

        if ($r)
        {
            $r = $r->result_array();
            $lx['records'] = $r;
        }

        $q = "SELECT COUNT(M_ItemID) n
            FROM m_item
            LEFT JOIN (
                SELECT Log_StockM_ItemID log_item_id, SUM(Log_StockBeforeQty) log_b4_qty,
                    SUM(Log_StockQty) log_qty, SUM(Log_StockAfterQty) log_a4_qty
                FROM xsales_log.log_stock 
                WHERE Log_StockIndex IN (
                    SELECT MAX(Log_StockIndex) idx
                    FROM xsales_log.log_stock
                    WHERE Log_StockIsActive = 'Y'
                    AND Log_StockDate <= ?
                    GROUP BY Log_StockM_ItemID, Log_StockM_WarehouseID)
                GROUP BY Log_StockM_ItemID
                ) x ON M_ItemID = log_item_id
            WHERE (log_qty IS NOT NULL OR Log_a4_qty Is NOT NULL)
            AND M_ItemIsActive = 'Y'
            AND (M_ItemName LIKE ? OR M_ItemCode LIKE ?)
            AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)
            AND log_a4_qty > 0";

        $r = $this->db->query(
            $q, [$edate, $d['search'], $d['search'], $category, $category, $category]);

        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }

        return $lx;
    }

    // Report Detail Persediaan
    function iv_007 ($d)
    {
        $category = isset($d['category_id'])?$d['category_id']:0;
        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2023-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $q = "SELECT M_ItemID item_id, M_ItemCode item_code, M_ItemName item_name,
                details, M_UnitName unit_name,
                M_ItemDefaultHPP item_hpp, M_CategoryName category_name
            FROM m_item
            JOIN m_unit ON M_ItemM_Unitid = M_UnitID
            JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
            LEFT JOIN (
                SELECT Log_StockM_ItemID log_item_id,
                    CONCAT('[', GROUP_CONCAT(
                    JSON_OBJECT('log_id', Log_StockID, 'log_item_id', Log_StockM_ItemID, 'log_before_qty', Log_StockBeforeQty,
                        'log_qty', Log_StockQty, 'log_after_qty', Log_StockAfterQty, 'log_ref_number', Log_StockRefNumber, 'log_ref_id', Log_StockRefID,
                        'log_date', date(Log_StockDate), 'log_type', Log_StockCode, 'log_type_text', Log_TypeText, 'log_type_name', Log_TypeName,
                        'warehouse_id', wa.M_WarehouseID, 'warehouse_name', wa.M_WarehouseName,
                        'fromto_warehouse_id', wb.M_WarehouseID, 'fromto_warehouse_name', wb.M_WarehouseName,
                        'customer_name', IFNULL(M_CustomerName, ''), 'vendor_name', IFNULL(M_VendorName, '')) ORDER BY Log_StockIndex ASC
                    ), ']') details
                FROM one_account_aw_log.log_stock
                JOIN m_warehouse wa ON Log_StockM_WarehouseID = wa.M_WarehouseID 
                LEFT JOIN m_warehouse wb ON Log_StockFromToM_WarehouseID = wb.M_WarehouseID
                LEFT JOIN m_customer ON Log_StockM_CustomerID = M_CustomerID
                LEFT JOIN m_vendor ON Log_StockM_SupplierID = M_VendorID
                LEFT JOIN one_account_aw_log.log_type ON Log_StockCode = Log_TypeCode
                WHERE Log_StockIsActive = 'Y'
                AND Log_StockDate BETWEEN ? AND ?
                GROUP BY Log_StockM_ItemID
                ORDER BY Log_StockM_ItemID ASC
                ) x ON M_ItemID = log_item_id
            WHERE (log_item_id IS NOT NULL)
            AND M_ItemIsActive = 'Y'
            AND (M_ItemName LIKE ? OR M_ItemCode LIKE ?)
            AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)
            ORDER BY M_ItemName ASC
            LIMIT {$limit} OFFSET {$offset}";
        
        $r = $this->db->query(
            $q, [$sdate, $edate, $d['search'], $d['search'], $category, $category, $category]);

        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $details = json_decode($v['details']);
                foreach ($details as $l => $w)
                {
                    $w = (array) $w;
                    $text = preg_replace("/\[(supplier)\]/", $w['vendor_name'], $w['log_type_text']);
                    $text = preg_replace("/\[(customer)\]/", $w['customer_name'], $text);
                    $text = preg_replace("/\[(warehouse)\]/", $w['warehouse_name'], $text);
                    $text = preg_replace("/\[(towarehouse)\]/", $w['fromto_warehouse_name'], $text);

                    $details[$l]->log_type_text = $text;
                }

                $r[$k]['details'] = $details;
            }
            $lx['records'] = $r;
        }

        $q = "SELECT COUNT(M_ItemID) n
            FROM m_item
            LEFT JOIN (
                SELECT Log_StockM_ItemID log_item_id, SUM(Log_StockBeforeQty) log_b4_qty,
                    SUM(Log_StockQty) log_qty, SUM(Log_StockAfterQty) log_a4_qty
                FROM one_account_aw_log.log_stock 
                WHERE Log_StockIndex IN (
                    SELECT MAX(Log_StockIndex) idx
                    FROM one_account_aw_log.log_stock
                    WHERE Log_StockIsActive = 'Y'
                    AND Log_StockDate BETWEEN ? AND ?
                    GROUP BY Log_StockM_ItemID, Log_StockM_WarehouseID)
                GROUP BY Log_StockM_ItemID
                ) x ON M_ItemID = log_item_id
            WHERE (log_qty IS NOT NULL OR Log_a4_qty Is NOT NULL)
            AND M_ItemIsActive = 'Y'
            AND (M_ItemName LIKE ? OR M_ItemCode LIKE ?)
            AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)"
            ;

        $r = $this->db->query(
            $q, [$sdate, $edate, $d['search'], $d['search'], $category, $category, $category]);

        if ($r)
        {
            $lx['total'] = $r->row()->n;
            $lx['total_page'] = ceil($r->row()->n / $limit);
        }

        return $lx;
    }

    // Report Nilai Stok Gudang
    function iv_008 ($d)
    {
        $category = isset($d['category_id'])?$d['category_id']:0;
        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2023-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");
        $limit = 999999; // isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        
        $lx = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $q = "SELECT M_WarehouseID warehouse_id, M_WarehouseName warehouse_name,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('item_id', item_id, 'item_code', item_code, 'item_name', item_name,
                    'log_b4_qty', log_b4_qty, 'log_qty', log_qty, 'log_a4_qty', log_a4_qty,
                    'unit_name', unit_name, 'item_hpp', item_hpp, 'category_name', category_name) ORDER BY item_name ASC), 
                    ']') as details
            FROM m_warehouse
            JOIN (
                SELECT M_ItemID item_id, M_ItemCode item_code, M_ItemName item_name,
                    log_b4_qty, log_qty, log_a4_qty, M_UnitName unit_name,
                    M_ItemDefaultHPP item_hpp, M_CategoryName category_name,
                    M_WarehouseID warehouse_id, M_WarehouseName warehouse_name
                FROM m_item
                JOIN m_unit ON M_ItemM_Unitid = M_UnitID
                JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
                LEFT JOIN (
                    SELECT Log_StockM_ItemID log_item_id, SUM(Log_StockBeforeQty) log_b4_qty,
                        SUM(Log_StockQty) log_qty, SUM(Log_StockAfterQty) log_a4_qty,
                        Log_StockM_WarehouseID log_warehouse_id
                    FROM one_account_aw_log.log_stock 
                    WHERE Log_StockIndex IN (
                        SELECT MAX(Log_StockIndex) idx
                        FROM one_account_aw_log.log_stock
                        WHERE Log_StockIsActive = 'Y'
                        AND Log_StockDate <= ?
                        GROUP BY Log_StockM_ItemID, Log_StockM_WarehouseID)
                    GROUP BY Log_StockM_ItemID, Log_StockM_WarehouseID
                    ) x ON M_ItemID = log_item_id
                JOIN m_warehouse ON log_warehouse_id = M_WarehouseID
                WHERE (log_qty IS NOT NULL OR Log_a4_qty Is NOT NULL)
                AND M_ItemIsActive = 'Y'
                AND (M_ItemName LIKE ? OR M_ItemCode LIKE ?)
                AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)
                AND log_a4_qty > 0
                ORDER BY M_WarehouseName, M_ItemName ASC
                LIMIT {$limit} OFFSET {$offset}
            ) y ON warehouse_id = M_WarehouseID
            GROUP BY M_WarehouseID
            ORDER BY M_WarehouseName ASC";
        
        $r = $this->db->query(
            $q, [$edate, $d['search'], $d['search'], $category, $category, $category]);

        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['details'] = json_decode($v['details']);
            }
            $lx['records'] = $r;
        }

        // $q = "SELECT COUNT(M_ItemID) n
        //     FROM m_item
        //     LEFT JOIN (
        //         SELECT Log_StockM_ItemID log_item_id, SUM(Log_StockBeforeQty) log_b4_qty,
        //             SUM(Log_StockQty) log_qty, SUM(Log_StockAfterQty) log_a4_qty
        //         FROM one_account_aw_log.log_stock 
        //         WHERE Log_StockIndex IN (
        //             SELECT MAX(Log_StockIndex) idx
        //             FROM one_account_aw_log.log_stock
        //             WHERE Log_StockIsActive = 'Y'
        //             AND Log_StockDate <= ?
        //             GROUP BY Log_StockM_ItemID, Log_StockM_WarehouseID)
        //         GROUP BY Log_StockM_ItemID
        //         ) x ON M_ItemID = log_item_id
        //     WHERE (log_qty IS NOT NULL OR Log_a4_qty Is NOT NULL)
        //     AND M_ItemIsActive = 'Y'
        //     AND (M_ItemName LIKE ? OR M_ItemCode LIKE ?)
        //     AND ((M_ItemM_CategoryID = ? AND ? > 0) OR ? = 0)
        //     AND log_a4_qty > 0";

        // $r = $this->db->query(
        //     $q, [$edate, $d['search'], $d['search'], $category, $category, $category]);

        // if ($r)
        // {
            $lx['total'] = 1; // $r->row()->n;
            $lx['total_page'] = 1; // ceil($r->row()->n / $limit);
        // }

        return $lx;
    }

    function iv_009 ($d)
    {
        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_iv009.sql");
        $r = $this->db->query($q, [$d['warehouse_id'], $d['warehouse_id'], $d['warehouse_id'], $d['search'], $d['search']])->result_array();

        return $r;
    }

    function iv_010 ($d)
    {
        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_iv010.sql");
        $r = $this->db->query($q, [$d['sdate'], $d['edate'], $d['warehouse_id']])->result_array();

        $highestRange = 50000;
        foreach ($r as $k => $v)
        {
            if ($k == 0) $highestRange = $v['item_qty'];
            $r[$k]['item_percent'] = $v['item_qty'] * 100 / $highestRange;
        }
        return $r;
    }

    function iv_011 ($d)
    {
        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_iv011.sql");
        $r = $this->db->query($q, [$d['edate'], 
            $d['warehouse_id'], $d['warehouse_id'], $d['warehouse_id'], 
            $d['sdate'], $d['edate'], 
            $d['warehouse_id'], $d['warehouse_id'], $d['warehouse_id'], 
            $d['warehouse_id'], $d['warehouse_id'], $d['warehouse_id'], 
            $d['sdate'], $d['edate'], 
            $d['warehouse_id'], $d['warehouse_id'], $d['warehouse_id'],
            $d['search'], $d['search']])->result_array();

        // classify
        $rst = [['id'=>'FM', 'title'=>'FAST MOVING', 'items'=>[], 'color'=>'green lighten-3'],
            ['id'=>'MM', 'title'=>'MEDIUM MOVING', 'items'=>[], 'color'=>'blue lighten-3'],
            ['id'=>'SM', 'title'=>'SLOW MOVING', 'items'=>[], 'color'=>'orange lighten-3'],
            ['id'=>'HSM', 'title'=>'HIGH SLOW MOVING', 'items'=>[], 'color'=>'yellow lighten-3']];
        foreach ($r as $k => $v) {
            if ($v['omzet_freq'] > 2) $rst[0]['items'][] = $v;
            else if ($v['omzet_freq'] == 2) $rst[1]['items'][] = $v;
            else if ($v['omzet_freq'] == 1) $rst[2]['items'][] = $v;
            else $rst[3]['items'][] = $v;
        }
        
        return $rst;
    }

    function iv_010_2 ($d)
    {
        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_iv010_2.sql");
        $r = $this->db->query($q, [$d['sdate'], $d['edate'], $d['sdate'], $d['edate']])->result_array();

        $highestRange = 50000000;
        foreach ($r as $k => $v)
        {
            if ($k == 0) $highestRange = $v['item_total'];
            $r[$k]['item_percent'] = $v['item_total'] * 100 / $highestRange;
        }
        return $r;
    }

    function iv_012 ($d)
    {
        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_iv012.sql");
        $r = $this->db->query($q, [$d['sdate'], $d['edate']])->result_array();

        $total_cnt = 0; 
        $total_sum = 0;
        foreach ($r as $k => $v)
        {
            $total_cnt += $v['delivery_count'];
            $total_sum += $v['delivery_item'];
        }

        foreach ($r as $k => $v)
        {
            $r[$k]['total_count'] = $total_cnt;
            $r[$k]['total_sum'] = $total_sum;
            $r[$k]['percentage_count'] = $v['delivery_count'] * 100 / $total_cnt;
            $r[$k]['percentage_sum'] = $v['delivery_item'] * 100 / $total_sum;
        }

        return $r;
    }
}

?>

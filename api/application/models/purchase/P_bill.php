<?php

class P_bill extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "p_bill";
        $this->table_key = "P_BillID";
    }

    function search( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['vendor_id'])?$d['vendor_id']:0;
        // $warehouse = isset($d['warehouse_id'])?$d['warehouse_id']:0;

        $r = $dbiv->query(
                "SELECT P_BillID bill_id, P_BillNumber bill_number, P_BillDate bill_date, P_BillTotal bill_total,
                P_BillNote bill_note, M_VendorName vendor_name,
                M_VendorID vendor_id,
                CONCAT('[',GROUP_CONCAT(JSON_OBJECT('receive_id',P_ReceiveID,'item_id',produk_id,'qty',P_BillDetailQty,'note',P_BillDetailNote)),']') items
                FROM `{$this->table_name}`
                JOIN m_vendor ON P_BillM_VendorID = M_VendorID
                JOIN erp.ms_cabang ON P_BillM_WarehouseID = cabang_id
                    LEFT JOIN p_billdetail ON P_BillID = P_BillDetailP_BillID AND P_BillDetailIsactive= 'Y'
                    LEFT JOIN p_purchasedetail ON P_BillDetailP_PurchaseDetailID = P_PurchaseDetailID
                    LEFT JOIN erp.ms_produk ON produk_id = P_PurchaseDetailA_ItemID
                WHERE `P_BillNumber` LIKE ?
                AND `P_BillIsActive` = 'Y'
                AND ((P_BillM_VendorID = ? AND ? > 0) OR ? = 0)
                AND ((P_BillM_WarehouseID = ? AND ? > 0) OR ? = 0)
                GROUP BY P_BillID
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
                $r[$k]['items'] = json_decode($v['items']);
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_vendor ON P_BillM_VendorID = M_VendorID
            JOIN erp.ms_cabang ON P_BillM_WarehouseID = cabang_id
            WHERE `P_BillNumber` LIKE ?
            AND ((P_BillM_VendorID = ? AND ? > 0) OR ? = 0)
            AND ((P_BillM_WarehouseID = ? AND ? > 0) OR ? = 0)
            AND `P_BillIsActive` = 'Y'", [$d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_old( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['supplier_id'])?$d['supplier_id']:0;
        $edits = isset($d['edits']) ? "OR FIND_IN_SET(P_BillID, '{$d['edits']}')" : "";

        $r = $dbiv->query(
                "SELECT P_BillID bill_id, P_BillNumber bill_number, P_BillDate bill_date, P_BillTotal bill_total,
                0 bill_paid, 0 bill_unpaid,
                P_BillDone bill_note,
                P_BillNote bill_note
                FROM `{$this->table_name}`
                WHERE `P_BillNumber` LIKE ?
                AND `P_BillIsActive` = 'Y'
                AND (`P_BillDone` = 'N' {$edits})
                AND ((P_BillM_VendorID = ? AND ? > 0) OR ? = 0)
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $supplier, $supplier, $supplier]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `P_BillNumber` LIKE ?
            AND (`P_BillLunas` = 'N' {$edits})
            AND ((P_BillM_VendorID = ? AND ? > 0) OR ? = 0)
            AND `P_BillIsActive` = 'Y'", [$d['search'], $supplier, $supplier, $supplier]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_autocomplete( $d )
    {
        $d['limit'] = 200;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function save ( $d, $id = 0, $uid )
    {
        $r = $this->db->query("CALL sp_purchase_bill_save(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
}

?>
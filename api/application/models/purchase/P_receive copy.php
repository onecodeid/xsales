<?php

class P_receive extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "p_receive";
        $this->table_key = "P_ReceiveID";
    }

    function search( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['vendor_id'])?$d['vendor_id']:0;
        $warehouse = isset($d['warehouse_id'])?$d['warehouse_id']:0;
        $bill_only = "";

        if (isset($d['bill_only']))
        {
            $bill_only = " AND P_ReceiveF_BillID=0";
            if(isset($d['edits']))
            {
                $bill_only = " AND (P_ReceiveF_BillID=0 OR FIND_IN_SET(P_ReceiveID, '{$d['edits']}'))";
            }
        }

        $r = $dbiv->query(
                "SELECT P_ReceiveID receive_id, P_ReceiveNumber receive_number, P_ReceiveDate receive_date, P_ReceiveTotalQty receive_total_qty,
                P_ReceiveNote receive_note, M_VendorName vendor_name,
                cabang_nama warehouse_name, M_VendorID vendor_id, cabang_id warehouse_id,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('item', JSON_OBJECT('detail_done', '',
                    'detail_id', p_purchasedetailid,
                    'detail_qty', p_purchasedetailqty,
                    'detail_received', p_purchasedetailreceived,
                    'detail_unreceived', p_purchasedetailunreceived,
                    'item_id', produk_id,
                    'item_name', produk_nama,
                    'purchase_date', DATE_FORMAT(P_PurchaseDate, '%d-%m-%Y'),
                    'purchase_number', P_PurchaseNumber,
                    'vendor_name', M_VendorName), 'note', P_ReceiveDetailNote, 'qty', P_ReceiveDetailQty, 'total', 0)),']') items


                FROM `{$this->table_name}`
                JOIN m_vendor ON P_ReceiveM_VendorID = M_VendorID
                JOIN erp.ms_cabang ON P_ReceiveM_WarehouseID = cabang_id
                    LEFT JOIN p_receivedetail ON P_ReceiveID = P_ReceiveDetailP_ReceiveID AND P_ReceiveDetailIsactive= 'Y'
                    LEFT JOIN p_purchasedetail ON P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
                    LEFT JOIN p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID
                    LEFT JOIN erp.ms_produk ON produk_id = P_PurchaseDetailA_ItemID
                WHERE `P_ReceiveNumber` LIKE ?
                AND `P_ReceiveIsActive` = 'Y'
                AND ((P_ReceiveM_VendorID = ? AND ? > 0) OR ? = 0)
                AND ((P_ReceiveM_WarehouseID = ? AND ? > 0) OR ? = 0)
                {$bill_only}
                GROUP BY P_ReceiveID
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $items = (array)json_decode($v['items']);
                // foreach ($items as $kk => $vv)
                // {
                //     $vv->subtotal = $vv->qty * $vv->price * (100-$vv->disc) / 100;
                //     if ($vv->ppn=="Y"&&$vv->include_ppn=="N")
                //         $vv->subtotal = $vv->subtotal * 1.1;
                //     $items[$kk] = $vv;
                // }
                $r[$k]['items'] = $items;
            }
                
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_vendor ON P_ReceiveM_VendorID = M_VendorID
            JOIN erp.ms_cabang ON P_ReceiveM_WarehouseID = cabang_id
            WHERE `P_ReceiveNumber` LIKE ?
            AND ((P_ReceiveM_VendorID = ? AND ? > 0) OR ? = 0)
            AND ((P_ReceiveM_WarehouseID = ? AND ? > 0) OR ? = 0)
            AND `P_ReceiveIsActive` = 'Y'
            {$bill_only}", [$d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse]);
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

    function search_dd( $d )
    {
        $d['limit'] = 200;
        $d['page'] = 1;
        $d['bill_only'] = true;
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function save ( $d, $id = 0, $uid )
    {
        $r = $this->db->query("CALL sp_purchase_receive_save(?,?,?,?)", [
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
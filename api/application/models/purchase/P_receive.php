<?php

class P_receive extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "p_receive";
        $this->table_key = "P_ReceiveID";
    }

    function search_4_invoice( $d )
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

        // single
        $receiveid = isset($d['receive_id'])?$d['receive_id']:0;

        $r = $dbiv->query(
                "SELECT P_ReceiveID receive_id, P_ReceiveSecondaryNumber receive_number, DATE_FORMAT(P_ReceiveDate, '%d-%m-%Y') receive_date, P_ReceiveTotalQty receive_total_qty,
                P_ReceiveNote receive_note, IFNULL(P_ReceiveMemo, '') receive_memo, M_VendorName vendor_name,
                M_WarehouseName warehouse_name, M_VendorID vendor_id, M_WarehouseID warehouse_id,
                CONCAT('[',GROUP_CONCAT(JSON_OBJECT('purchase_id',P_PurchaseDetailID,'purchase_memo',IFNULL(P_PurchaseMemo, ''),'item_id',M_ItemID,'item_name',M_ItemName,'item_unit',M_UnitName,'qty',P_ReceiveDetailQty,'price',P_PurchaseDetailPrice,'disc',P_PurchaseDetailDisc,'discrp',P_PurchaseDetailDiscRp,'ppn',P_PurchaseDetailPPN,'ppn_amount',P_PurchaseDetailPPNAmount,'include_ppn',P_PurchaseIncludePPN,'note',P_ReceiveDetailNote)),']') items
                FROM `{$this->table_name}`
                JOIN m_vendor ON P_ReceiveM_VendorID = M_VendorID
                JOIN m_warehouse ON P_ReceiveM_WarehouseID = M_WarehouseID
                    LEFT JOIN p_receivedetail ON P_ReceiveID = P_ReceiveDetailP_ReceiveID AND P_ReceiveDetailIsactive= 'Y'
                    LEFT JOIN p_purchasedetail ON P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
                    LEFT JOIN p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID
                    LEFT JOIN m_item ON M_ItemID = P_PurchaseDetailA_ItemID
                    LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                WHERE `P_ReceiveSecondaryNumber` LIKE ?
                AND `P_ReceiveIsActive` = 'Y'
                AND ((P_ReceiveM_VendorID = ? AND ? > 0) OR ? = 0)
                AND ((P_ReceiveM_WarehouseID = ? AND ? > 0) OR ? = 0) 
                AND ((P_ReceiveID = ? AND ? <> 0) OR ? = 0)
                {$bill_only}
                GROUP BY P_ReceiveID
                ORDER BY P_ReceiveDate DESC, P_ReceiveNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse,
                    $receiveid, $receiveid, $receiveid]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $items = (array)json_decode($v['items']);
                foreach ($items as $kk => $vv)
                {
                    $vv->subtotal = ($vv->qty * $vv->price * (100-$vv->disc) / 100) - ($vv->qty * $vv->discrp);

                    if ($vv->ppn=="Y"&&$vv->include_ppn=="Y")
                    {
                        $vv->price = $vv->price / 1.1;
                        $y = ($vv->qty * $vv->price * (100-$vv->disc) / 100) - ($vv->qty * $vv->discrp);
                        $vv->ppn_amount = $vv->subtotal - $y;
                    }
                    
                    if ($vv->ppn=="Y"&&$vv->include_ppn=="N")
                        $vv->ppn_amount = $vv->subtotal * 0.1;

                        // $vv->subtotal = $vv->subtotal * 1.1;
                    $items[$kk] = $vv;
                }
                $r[$k]['items'] = $items;
            }
                
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_vendor ON P_ReceiveM_VendorID = M_VendorID
            JOIN m_warehouse ON P_ReceiveM_WarehouseID = M_WarehouseID
            WHERE `P_ReceiveSecondaryNumber` LIKE ?
            AND ((P_ReceiveM_VendorID = ? AND ? > 0) OR ? = 0)
            AND ((P_ReceiveM_WarehouseID = ? AND ? > 0) OR ? = 0)
            AND `P_ReceiveIsActive` = 'Y'
            AND ((P_ReceiveID = ? AND ? <> 0) OR ? = 0)
            {$bill_only}", [$d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse,
                $receiveid, $receiveid, $receiveid]);
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
        $r = $this->search_4_invoice( $d );
        
        return $r['records'];
    }

    function save ( $d, $id = 0, $uid )
    {
        $hdata = json_decode($d['hdata']);
        $hdata->p_date = date("Y-m-d", strtotime($hdata->p_date));
        $d['hdata'] = json_encode($hdata);
        
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

    function delete ( $id, $uid )
    {
        $r = $this->db->query("CALL sp_purchase_receive_delete(?,?)", [
                        $id,
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function confirm ( $id, $uid )
    {
        $r = $this->db->query("CALL sp_purchase_receive_confirm(?,?)", [
                        $id,
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function bill_create ( $id, $d, $uid = 0 )
    {
        $r = $this->db->query("CALL sp_purchase_receive_bill_create(?,?,?)", [
                        $id,
                        json_encode($d),
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
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

        // single
        $receiveid = isset($d['receive_id'])?$d['receive_id']:0;

        $r = $dbiv->query(
                "SELECT P_ReceiveID receive_id, P_ReceiveSecondaryNumber receive_number, P_ReceiveRefNumber receive_ref_number, DATE_FORMAT(P_ReceiveDate, '%d-%m-%Y') receive_date, P_ReceiveTotalQty receive_total_qty, P_ReceiveConfirm receive_confirm,
                P_ReceiveNote receive_note, P_ReceiveMemo receive_memo, P_ReceiveF_BillID receive_bill, M_VendorName vendor_name,
                M_WarehouseName warehouse_name, M_VendorID vendor_id, M_WarehouseID warehouse_id,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('item', JSON_OBJECT('detail_done', '',
                    'detail_id', p_purchasedetailid,
                    'detail_qty', p_purchasedetailqty,
                    'detail_received', p_purchasedetailreceived,
                    'detail_unreceived', p_purchasedetailunreceived,
                    'item_id', M_ItemID,
                    'item_name', M_ItemName,
                    'item_unit', IFNULL(M_UnitName, ''),
                    'purchase_date', DATE_FORMAT(P_PurchaseDate, '%d-%m-%Y'),
                    'purchase_number', P_PurchaseNumber,
                    'purchase_memo', IFNULL(P_PurchaseMemo, ''),
                    'vendor_name', M_VendorName), 'note', P_ReceiveDetailNote, 'qty', P_ReceiveDetailQty, 'total', 0)),']') items,

                IFNULL(GROUP_CONCAT(distinct P_PurchaseMemo SEPARATOR ';'), '') purchase_memos


                FROM `{$this->table_name}`
                JOIN m_vendor ON P_ReceiveM_VendorID = M_VendorID
                JOIN m_warehouse ON P_ReceiveM_WarehouseID = M_WarehouseID
                    LEFT JOIN p_receivedetail ON P_ReceiveID = P_ReceiveDetailP_ReceiveID AND P_ReceiveDetailIsactive= 'Y'
                    LEFT JOIN p_purchasedetail ON P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
                    LEFT JOIN p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID
                    LEFT JOIN m_item ON M_ItemID = P_PurchaseDetailA_ItemID
                    LEFT JOIN m_unit ON M_UnitID = M_ItemM_UnitID
                WHERE (`P_ReceiveSecondaryNumber` LIKE ? OR M_VendorName LIKE ?)
                AND `P_ReceiveIsActive` = 'Y'
                AND ((P_ReceiveM_VendorID = ? AND ? > 0) OR ? = 0)
                AND ((P_ReceiveM_WarehouseID = ? AND ? > 0) OR ? = 0)
                AND P_ReceiveDate BETWEEN ? AND ?
                AND ((P_ReceiveID = ? AND ? <> 0) OR ? = 0)
                {$bill_only}
                GROUP BY P_ReceiveID
                ORDER BY P_ReceiveDate DESC, P_ReceiveSecondaryNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse, $d['sdate'], $d['edate'],
                    $receiveid, $receiveid, $receiveid]);
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
            JOIN m_warehouse ON P_ReceiveM_WarehouseID = M_WarehouseID
            WHERE (`P_ReceiveSecondaryNumber` LIKE ? OR M_VendorName LIKE ?)
            AND ((P_ReceiveM_VendorID = ? AND ? > 0) OR ? = 0)
            AND ((P_ReceiveM_WarehouseID = ? AND ? > 0) OR ? = 0)
            AND `P_ReceiveIsActive` = 'Y'
            AND P_ReceiveDate BETWEEN ? AND ?
            AND ((P_ReceiveID = ? AND ? <> 0) OR ? = 0)
            {$bill_only}", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $warehouse, $warehouse, $warehouse, $d['sdate'], $d['edate'],
                    $receiveid, $receiveid, $receiveid]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function get_parent_id($id)
    {
        $r = $this->db->where('P_ReceiveDetailID', $id)
                    ->get('p_receivedetail')
                    ->row();
        return $r->P_ReceiveDetailP_ReceiveID;
    }
}

?>
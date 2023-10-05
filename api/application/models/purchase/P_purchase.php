<?php

class P_Purchase extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "p_purchase";
        $this->table_key = "P_PurchaseID";
    }

    function search( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['supplier_id'])?$d['supplier_id']:0;
        $edits = isset($d['edits']) ? "OR FIND_IN_SET(P_PurchaseID, '{$d['edits']}')" : "";
        $done = isset($d['done']) ? "AND P_PurchaseDone = '{$d['done']}'" : "";

        // single
        $purchaseid = isset($d['purchase_id'])?$d['purchase_id']:0;

        $r = $dbiv->query(
                "SELECT P_PurchaseID purchase_id, P_PurchaseNumber purchase_number, DATE_FORMAT(P_PurchaseDate, '%d-%m-%Y') purchase_date, 
                P_PurchaseTotal purchase_total, P_PurchaseGrandTotal purchase_grandtotal, P_PurchaseDP purchase_dp,
                P_PurchaseShipping purchase_shipping, P_PurchaseDisc purchase_disc, P_PurchaseDiscRp purchase_discrp,
                0 purchase_paid, 0 purchase_unpaid,
                P_PurchaseDone purchase_done,
                P_PurchaseNote purchase_note, P_PurchaseMemo purchase_memo,
                P_PurchaseIncludePPN purchase_ppn, P_PurchasePPN purchase_ppn_amount,
                M_VendorName vendor_name, M_VendorID vendor_id,
                P_PurchaseM_PaymentPlanID purchase_payment, P_PurchaseM_TermID term_id, P_PurchaseS_StaffID purchase_staff,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('item', JSON_OBJECT('item_id', M_ItemID,'item_code', M_ItemCode,'item_name', M_ItemName), 'price', P_PurchaseDetailPrice, 'qty', P_PurchaseDetailQty, 
                    'received', P_PurchaseDetailReceived, 'unreceived', P_PurchaseDetailUnReceived, 'unit', IFNULL(M_UnitName, ''), 'disc', P_PurchaseDetailDisc, 
                    'discrp', P_PurchaseDetailDiscRp, 'disctype', IF(P_PurchaseDetailDiscRp=0,'P','R'),'ppn', P_PurchaseDetailPPN,'ppn_amount', P_PurchaseDetailPPNAmount, 'subtotal', P_PurchaseDetailSubTotal, 'total', P_PurchaseDetailTotal)), ']') details,
                IFNULL(M_PaymentPlanName, '') payment_name, IFNULL(M_TermName, '') term_name

                FROM `{$this->table_name}`
                JOIN m_vendor ON P_PurchaseM_VendorID = M_VendorID
                    LEFT JOIN p_purchasedetail ON P_PurchaseDetailP_PurchaseID=P_PurchaseID and P_PurchaseDetailIsActive = 'Y'
                    LEFT JOIN m_item ON M_ItemID = P_PurchaseDetailA_ItemID
                    LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                    LEFT JOIN m_paymentplan ON P_PurchaseM_PaymentPlanID = M_PaymentPlanID
                    LEFT JOIN m_term ON P_PurchaseM_TermID = M_TermID
                    
                WHERE (`P_PurchaseNumber` LIKE ? OR M_VendorName LIKE ?)
                AND `P_PurchaseIsActive` = 'Y'
                AND ((P_PurchaseM_VendorID = ? AND ? > 0) OR ? = 0)
                AND P_PurchaseDate BETWEEN ? AND ?
                AND ((P_PurchaseID = ? AND ? <> 0) OR ? = 0)
                {$done}
                GROUP BY P_PurchaseID
                ORDER BY P_PurchaseDate DESC, P_PurchaseNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $d['sdate'], $d['edate'],
                    $purchaseid, $purchaseid, $purchaseid]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['details'] = json_decode($v['details']);
                foreach ($r[$k]['details'] as $m => $w)
                {
                    $r[$k]['details'][$m]->itemtotal = (($w->price * (100-$w->disc) / 100) - $w->discrp) * $w->qty;
                }
            }
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_vendor ON P_PurchaseM_VendorID = M_VendorID
            WHERE (`P_PurchaseNumber` LIKE ? OR M_VendorName LIKE ?)
            AND ((P_PurchaseM_VendorID = ? AND ? > 0) OR ? = 0)
            AND P_PurchaseDate BETWEEN ? AND ?
            AND ((P_PurchaseID = ? AND ? <> 0) OR ? = 0)
            {$done}
            AND `P_PurchaseIsActive` = 'Y'", [$d['search'], $d['search'], $supplier, $supplier, $supplier, $d['sdate'], $d['edate'],
                    $purchaseid, $purchaseid, $purchaseid]);
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
        $edits = isset($d['edits']) ? "OR FIND_IN_SET(P_PurchaseID, '{$d['edits']}')" : "";

        $r = $dbiv->query(
                "SELECT P_PurchaseID purchase_id, P_PurchaseNumber purchase_number, P_PurchaseDate purchase_date, P_PurchaseTotal purchase_total,
                0 purchase_paid, 0 purchase_unpaid,
                P_PurchaseDone purchase_note,
                P_PurchaseNote purchase_note
                FROM `{$this->table_name}`
                WHERE `P_PurchaseNumber` LIKE ?
                AND `P_PurchaseIsActive` = 'Y'
                AND (`P_PurchaseDone` = 'N' {$edits})
                AND ((P_PurchaseM_VendorID = ? AND ? > 0) OR ? = 0)
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $supplier, $supplier, $supplier]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `P_PurchaseNumber` LIKE ?
            AND (`P_PurchaseLunas` = 'N' {$edits})
            AND ((P_PurchaseM_VendorID = ? AND ? > 0) OR ? = 0)
            AND `P_PurchaseIsActive` = 'Y'", [$d['search'], $supplier, $supplier, $supplier]);
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
        $hdata = json_decode($d['hdata']);
        $hdata->p_date = date("Y-m-d", strtotime($hdata->p_date));
        $d['hdata'] = json_encode($hdata);

        $r = $this->db->query("CALL sp_purchase_save(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function delete ($id, $uid = 0)
    {
        $r = $this->db->query("CALL sp_purchase_delete(?,?)", [$id, $uid])->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
}

?>
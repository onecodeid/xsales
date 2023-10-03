<?php

class P_purchaseretur extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "p_purchaseretur";
        $this->table_key = "P_PurchaseReturID";
    }

    function search( $d )
    {
        $dbiv = $this->load->database('dbiv', true);
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $supplier = isset($d['supplier_id'])?$d['supplier_id']:0;
        $edits = isset($d['edits']) ? "OR FIND_IN_SET(P_PurchaseReturID, '{$d['edits']}')" : "";

        $r = $dbiv->query(
                "SELECT P_PurchaseReturID retur_id, P_PurchaseReturNumber retur_number, DATE_FORMAT(P_PurchaseReturDate, '%d-%m-%Y') retur_date, P_PurchaseReturTotal retur_total,
                P_PurchaseReturUsed retur_used, P_PurchaseReturUnused retur_unused
                FROM `{$this->table_name}`
                WHERE `P_PurchaseReturNumber` LIKE ?
                AND `P_PurchaseReturMetaActive` = 'Y'
                AND ((P_PurchaseReturA_SupplierID = ? AND ? > 0) OR ? = 0)
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $supplier, $supplier, $supplier]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `P_PurchaseReturNumber` LIKE ?
            AND ((P_PurchaseReturA_SupplierID = ? AND ? > 0) OR ? = 0)
            AND `P_PurchaseReturMetaActive` = 'Y'", [$d['search'], $supplier, $supplier, $supplier]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_autocomplete( $d )
    {
        $d['limit'] = 100;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }
}

?>
<?php

/**
 * undocumented class
 */
class I_adjust extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "i_adjust";
        $this->table_key = "I_AdjustID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];
        $adjust_id = isset($d['adjust_id'])?$d['adjust_id']:0;

        $r = $this->db->query(
                "SELECT i_adjust.*, GROUP_CONCAT(M_ItemNAme SEPARATOR ', ') item_names, M_WarehouseName warehouse_name
                FROM `{$this->table_name}`
                JOIN i_adjustdetail ON I_AdjustID = I_AdjustDetailI_AdjustID AND I_AdjustDetailIsactive = 'Y'
                JOIN m_item ON I_AdjustDetailM_ItemID = M_ItemID
                JOIN m_warehouse ON I_AdjustM_WarehouseID = M_WarehouseID
                WHERE (`I_AdjustNumber` LIKE ? OR M_ItemName LIKE ?)
                AND I_AdjustDate BETWEEN ? AND ?
                AND `I_AdjustIsActive` = 'Y'
                AND ((I_AdjustM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                AND ((I_AdjustID = ? AND ? <> 0) OR ? = 0)
                GROUP BY I_AdjustID", [$d['search'], $d['search'], $d['sdate'], $d['edate'], $d['warehouse'], $d['warehouse'], $d['warehouse'], $adjust_id, $adjust_id, $adjust_id]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN i_adjustdetail ON I_AdjustID = I_AdjustDetailI_AdjustID AND I_AdjustDetailIsactive = 'Y'
                JOIN m_item ON I_AdjustDetailM_ItemID = M_ItemID
            WHERE (`I_AdjustNumber` LIKE ? OR M_ItemName LIKE ?)
                AND I_AdjustDate BETWEEN ? AND ?
                AND `I_AdjustIsActive` = 'Y'
                AND ((I_AdjustM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                AND ((I_AdjustID = ? AND ? <> 0) OR ? = 0)", [$d['search'], $d['search'], $d['sdate'], $d['edate'], $d['warehouse'], $d['warehouse'], $d['warehouse'], $adjust_id, $adjust_id, $adjust_id]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save( $d )
    {
        $r = $this->db->query("CALL sp_inventory_adjust_save(?, ?, ?, ?)", [$d['adjust_id'], $d['hdata'], $d['jdata'], $d['uid']])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    } 
}

?>
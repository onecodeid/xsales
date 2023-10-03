<?php

/**
 * undocumented class
 */
class I_transfer extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "i_transfer";
        $this->table_key = "I_TransferID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];
        $transfer_id = isset($d['transfer_id'])?$d['transfer_id']:0;

        $r = $this->db->query(
                "SELECT i_transfer.*, GROUP_CONCAT(M_ItemName SEPARATOR ', ') item_names, 
                    f.M_WarehouseName from_warehouse_name, t.M_WarehouseName to_warehouse_name
                FROM `{$this->table_name}`
                JOIN i_transferdetail ON I_TransferID = I_TransferDetailI_TransferID AND I_TransferDetailIsactive = 'Y'
                JOIN m_item ON I_TransferDetailM_ItemID = M_ItemID
                JOIN m_warehouse f ON I_TransferM_WarehouseID = f.M_WarehouseID
                JOIN m_warehouse t ON I_TransferToM_WarehouseID = t.M_WarehouseID
                WHERE (`I_TransferNumber` LIKE ? OR M_ItemName LIKE ?)
                AND I_TransferDate BETWEEN ? AND ?
                AND `I_TransferIsActive` = 'Y'
                AND ((I_TransferM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                AND ((I_TransferToM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                AND ((I_TransferID = ? AND ? <> 0) OR ? = 0)
                GROUP BY I_TransferID", [$d['search'], $d['search'], $d['sdate'], $d['edate'],
                    $d['from'],$d['from'],$d['from'],$d['to'],$d['to'],$d['to'], $transfer_id, $transfer_id, $transfer_id]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN i_transferdetail ON I_TransferID = I_TransferDetailI_TransferID AND I_TransferDetailIsactive = 'Y'
                JOIN m_item ON I_TransferDetailM_ItemID = M_ItemID
            WHERE (`I_TransferNumber` LIKE ? OR M_ItemName LIKE ?)
                AND I_TransferDate BETWEEN ? AND ?
                AND `I_TransferIsActive` = 'Y'
                AND ((I_TransferM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                AND ((I_TransferToM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                AND ((I_TransferID = ? AND ? <> 0) OR ? = 0)", [$d['search'], $d['search'], $d['sdate'], $d['edate'],
                    $d['from'],$d['from'],$d['from'],$d['to'],$d['to'],$d['to'], $transfer_id, $transfer_id, $transfer_id]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save( $d )
    {
        $r = $this->db->query("CALL sp_inventory_transfer_save(?, ?, ?, ?)", [$d['transfer_id'], $d['hdata'], $d['jdata'], $d['uid']])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    } 
}

?>
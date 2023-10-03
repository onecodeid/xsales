<?php

/**
 * undocumented class
 */
class I_assembly extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "i_assembly";
        $this->table_key = "I_AssemblyID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];
        $assembly_id = isset($d['assembly_id'])?$d['assembly_id']:0;

        $r = $this->db->query(
                "SELECT i_assembly.*, GROUP_CONCAT(ia.M_ItemName SEPARATOR ', ') item_names, 
                    M_WarehouseName warehouse_name, JSON_OBJECT('warehouse_id', M_WarehouseID, 'warehouse_name', M_WarehouseName) warehouse,
                    ib.M_ItemName out_item_name, I_AssemblyOutQty out_item_qty,
                    IFNULL(ub.M_UnitName, '') unit_name,
                    CONCAT('[', GROUP_CONCAT(JSON_OBJECT('item', JSON_OBJECT('item_id',ia.M_ItemID,'item_name',ia.M_ItemName,'item_code',ia.M_ItemCode,'I_StockQty',0, 'unit_name', IFNULL(ua.M_UnitName, '')), 
                        'qty', I_AssemblyDetailQty, 'hpp', I_AssemblyDetailHPP )), ']') details,
                    JSON_OBJECT('item_id', ib.M_ItemID, 'item_name', ib.M_ItemName, 'unit_id', IFNULL(ub.M_UnitID, 0), 'unit_name', IFNULL(ub.M_UnitName, '')) out_item,
                    I_AssemblyCosts costs
                FROM `{$this->table_name}`
                JOIN i_assemblydetail ON I_AssemblyID = I_AssemblyDetailI_AssemblyID AND I_AssemblyDetailIsactive = 'Y'
                JOIN m_item ia ON I_AssemblyDetailM_ItemID = ia.M_ItemID
                JOIN m_item ib ON I_AssemblyOutM_ItemID = ib.M_ItemID
                LEFT JOIN m_unit ub ON ib.M_ItemM_UnitID = ub.M_UnitID
                LEFT JOIN m_unit ua ON ia.M_ItemM_UnitID = ua.M_UnitID
                JOIN m_warehouse ON I_AssemblyM_WarehouseID = M_WarehouseID
                WHERE (`I_AssemblyNumber` LIKE ? OR ib.M_ItemName LIKE ?)
                AND I_AssemblyDate BETWEEN ? AND ?
                AND `I_AssemblyIsActive` = 'Y'
                AND ((I_AssemblyM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                AND ((I_AssemblyID = ? AND ? <> 0) OR ? = 0)
                GROUP BY I_AssemblyID", [$d['search'], $d['search'], $d['sdate'], $d['edate'], $d['warehouse'], $d['warehouse'], $d['warehouse'], $assembly_id, $assembly_id, $assembly_id]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['details'] = json_decode($v['details']);
                $r[$k]['warehouse'] = json_decode($v['warehouse']);
                $r[$k]['out_item'] = json_decode($v['out_item']);
                $r[$k]['costs'] = json_decode($v['costs']);
            }
                
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN i_assemblydetail ON I_AssemblyID = I_AssemblyDetailI_AssemblyID AND I_AssemblyDetailIsactive = 'Y'
                JOIN m_item ON I_AssemblyDetailM_ItemID = M_ItemID
            WHERE (`I_AssemblyNumber` LIKE ? OR M_ItemName LIKE ?)
                AND I_AssemblyDate BETWEEN ? AND ?
                AND `I_AssemblyIsActive` = 'Y'
                AND ((I_AssemblyM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                AND ((I_AssemblyID = ? AND ? <> 0) OR ? = 0)", [$d['search'], $d['search'], $d['sdate'], $d['edate'], $d['warehouse'], $d['warehouse'], $d['warehouse'], $assembly_id, $assembly_id, $assembly_id]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save( $d )
    {
        $r = $this->db->query("CALL sp_inventory_assembly_save(?, ?, ?, ?, ?)", [$d['assembly_id'], $d['hdata'], $d['jdata'], isset($d['accdata'])?$d['accdata']:"[]", $d['uid']])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    } 

    function delete ($id, $uid = 0)
    {
        $r = $this->db->query("CALL sp_inventory_assembly_delete(?,?)", [$id, $uid])->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
}

?>
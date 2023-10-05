<?php

class M_item extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_item";
        $this->table_key = "M_ItemID";
    }

    function search( $d )
    {
        $dblog = $this->load->database('dblog', true);

        // $dberp = $this->load->database('dberp', true);
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $assembly = isset($d['assembly']) ? $d['assembly'] : "";
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT M_ItemID item_id, M_ItemCode item_code, M_ItemName item_name, M_ItemAlias item_alias,
                CONCAT(M_ItemCode, ' - ', M_ItemName) item_code_name,
                M_ItemDefaultHPP item_hpp, M_ItemDefaultPrice item_price, M_CategoryID category_id,
                IFNULL(M_CategoryName, '') category_name, 
                IFNULL(M_UnitName, '') unit_name, IFNULL(M_UnitID, 0) unit_id,
                M_ItemMinStock item_min_stock, IFNULL(M_PackID, 0) pack_id,
                CONCAT('[', GROUP_CONCAT( JSON_OBJECT('warehouse_id', M_WarehouseID, 'warehouse_name', M_WarehouseName, 'warehouse_short', M_WarehouseShortName, 'stock_qty', I_StockQty, 'stock_min', I_StockMinQty) ), ']') stocks,
                SUM(IFNULL(I_StockQty, 0)) stock,
                IFNULL(Log_ItemPurchaseDetails, '[]') log_purchase, M_ItemViewInPack view_pack,
                M_ItemIsAssembly item_assembly,
                M_ItemAssemblyQty item_assembly_qty,
                M_ItemAssemblyNote item_assembly_note,
                M_ItemAssemblyCost item_assembly_cost,
                M_ItemAssemblyCosts item_assembly_costs,

                IFNULL(M_DiscID, 0) disc_id,
                IFNULL(M_DiscName, '') disc_name,
                IFNULL(M_DiscAmount, 0) disc_amount,

                IFNULL(I_StockQty, 0) stock_qty

                FROM `{$this->table_name}`
                LEFT JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
                LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
                LEFT JOIN m_pack ON M_ItemM_PackID = M_PackID
                LEFT JOIN m_warehouse ON M_WarehouseIsActive = 'Y'
                LEFT JOIN i_stock ON M_ItemID = I_StockM_ItemID AND I_StockIsActive = 'Y' AND I_StockM_WarehouseID = M_WarehouseID
                LEFT JOIN m_disc ON M_ItemM_DiscID = M_DiscID
                
                LEFT JOIN {$dblog->database}.log_itempurchase ON Log_ItemPurchaseM_ItemID = M_ItemID
                 
                WHERE (`M_ItemName` LIKE ? Or M_ItemCode LIKE ?)
                AND M_ItemIsActive = 'Y'
                AND ((M_ItemIsAssembly = ? AND ? <> '') OR ? = '')
                GROUP BY M_ItemID
                ORDER BY M_ItemCode
                LIMIT {$limit} OFFSET {$offset}", [$d['item_name'], $d['item_name'], 
                    $assembly, $assembly, $assembly]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v) {
                $r[$k]['stocks'] = json_decode($v['stocks']);
                $purchase = json_decode($v['log_purchase']);
                $r[$k]['log_purchase'] = $purchase == null ? [] : $purchase;
                
                $assemblies = $this->db->query("SELECT fn_master_item_assemblies(?) as x", [$v['item_id']])->row();
                $r[$k]['assemblies'] = json_decode($assemblies->x);
                if ($r[$k]['assemblies'] == null) $r[$k]['assemblies'] = [];
                $r[$k]['costs'] = json_decode($v['item_assembly_costs']);
            }
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE (`M_ItemName` LIKE ? Or M_ItemCode LIKE ?) AND M_ItemIsActive = 'Y'
            AND ((M_ItemIsAssembly = ? AND ? <> '') OR ? = '')", [$d['item_name'], $d['item_name'],
                $assembly, $assembly, $assembly]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function save ( $d, $id = 0 )
    {
        $r = $this->db->query("CALL sp_master_item_save(?,?,?,?,?,?)", [
                $id, 
                $d['hdata'],
                $d['aliases'],
                isset($d['packs'])?$d['packs']:"[]",
                isset($d['stockmins'])?$d['stockmins']:"[]",
                $d['user_id']
            ])
            ->row();

        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function del ($id)
    {
        $this->db->set('M_ItemIsActive', 'N')
                ->where('M_ItemID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }

    function set_slow ( $d )
    {
        $r = $this->db->query("CALL sp_master_item_slow(?,?,?)", [
                $d['item_id'], 
                $d['warehouse_id'],
                $d['value']
            ])
            ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
}

?>
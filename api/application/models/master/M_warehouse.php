<?php

class M_warehouse extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_warehouse";
        $this->table_key = "M_WarehouseID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT M_WarehouseID warehouse_id, M_WarehouseName warehouse_name, M_WarehouseShortName warehouse_short_name
                FROM `{$this->table_name}`
                WHERE `M_WarehouseName` LIKE ?
                LIMIT {$limit} OFFSET {$offset}", [$d['warehouse_name']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_WarehouseName` LIKE ?", [$d['warehouse_name']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_VendorName', $d['vendor_name'])
                    ->set('M_VendorCode', $d['vendor_code']);
                    // ->set('M_VendorUserID', $d['user_id']);
        if (isset($d['vendor_id']))
        {
            $this->db->where('M_VendorID', $d['vendor_id'])
                ->update( $this->table_name );
            $id = $d['vendor_id'];
        }
        else
        {
            $this->db->insert( $this->table_name );
            $id = $this->db->insert_id();
        }

        if ($r)
        {
            return (object) ["status"=>"OK", "data"=>$id];
        }

        return (object) ["status"=>"ERR"];
    }

    function del ($id)
    {
        $this->db->set('M_VendorIsActive', 'N')
                ->where('M_VendorID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }

    function get( $id )
    {
        $r = $this->db->query(
                "SELECT M_WarehouseID warehouse_id, M_WarehouseName warehouse_name, M_WarehouseShortName warehouse_short_name
                FROM `{$this->table_name}`
                WHERE `M_WarehouseID` = ?", [$id])->row();
            
        return $r;
    }
}

?>
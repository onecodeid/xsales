<?php

class M_driver extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_driver";
        $this->table_key = "M_DriverID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT M_DriverID driver_id, M_DriverName driver_name, IFNULL(M_DriverVehicle, '') driver_vehicle,
                    IFNULL(M_DriverPlatNumber, '') driver_plat, IFNULL(M_DriverPhone1, '') driver_phone1,
                    IFNULL(M_DriverPhone2, '') driver_phone2, IFNULL(M_DriverNote, '') driver_note,
                    IFNULL(M_DriverPool, '') driver_pool, IFNULL(M_DriverWeight, 0) driver_weight
                FROM `{$this->table_name}`
                WHERE `M_DriverName` LIKE ?
                AND `M_DriverIsActive` = 'Y'
                ORDER BY M_DriverName asc
                LIMIT {$limit} OFFSET {$offset}", [$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_DriverName` LIKE ?
            AND `M_DriverIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_DriverName', $d['driver_name'])
                    ->set('M_DriverPlatNumber', isset($d['driver_plat'])?$d['driver_plat']:'')
                    ->set('M_DriverVehicle', isset($d['driver_vehicle'])?$d['driver_vehicle']:'')
                    ->set('M_DriverPool', isset($d['driver_pool'])?$d['driver_pool']:'')
                    ->set('M_DriverPhone1', isset($d['driver_phone1'])?$d['driver_phone1']:'')
                    ->set('M_DriverPhone2', isset($d['driver_phone2'])?$d['driver_phone2']:'')
                    ->set('M_DriverWeight', isset($d['driver_weight'])?$d['driver_weight']:'')
                    ->set('M_DriverNote', isset($d['driver_note'])?$d['driver_note']:'');
                    // ->set('M_DriverUserID', $d['user_id']);
        if (isset($d['driver_id']))
        {
            $this->db->where('M_DriverID', $d['driver_id'])
                ->update( $this->table_name );
            $id = $d['driver_id'];
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
        $this->db->set('M_DriverIsActive', 'N')
                ->where('M_DriverID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>
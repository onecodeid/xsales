<?php

class M_expedition extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_expedition";
        $this->table_key = "M_ExpeditionID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT M_ExpeditionID expedition_id, M_ExpeditionCode expedition_code, M_ExpeditionName expedition_name,
                    IFNULL(M_ExpeditionAddress, '') expedition_address, IFNULL(M_ExpeditionPhone1, '') expedition_phone1,
                    IFNULL(M_ExpeditionWebsite, '') expedition_website, IFNULL(M_ExpeditionNote, '') expedition_note,
                    IFNULL(M_ExpeditionDestination, '') expedition_destination
                FROM `{$this->table_name}`
                WHERE `M_ExpeditionName` LIKE ?
                AND `M_ExpeditionIsActive` = 'Y'
                ORDER BY M_ExpeditionName asc
                LIMIT {$limit} OFFSET {$offset}", [$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_ExpeditionName` LIKE ?
            AND `M_ExpeditionIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_ExpeditionName', $d['expedition_name'])
                    ->set('M_ExpeditionCode', isset($d['expedition_code'])?$d['expedition_code']:'')
                    ->set('M_ExpeditionWebsite', isset($d['expedition_website'])?$d['expedition_website']:'')
                    ->set('M_ExpeditionAddress', isset($d['expedition_address'])?$d['expedition_address']:'')
                    ->set('M_ExpeditionPhone1', isset($d['expedition_phone1'])?$d['expedition_phone1']:'')
                    ->set('M_ExpeditionPhone2', isset($d['expedition_phone2'])?$d['expedition_phone2']:'')
                    ->set('M_ExpeditionDestination', isset($d['expedition_destination'])?$d['expedition_destination']:'')
                    ->set('M_ExpeditionNote', isset($d['expedition_note'])?$d['expedition_note']:'');
                    // ->set('M_ExpeditionUserID', $d['user_id']);
        if (isset($d['expedition_id']))
        {
            $this->db->where('M_ExpeditionID', $d['expedition_id'])
                ->update( $this->table_name );
            $id = $d['expedition_id'];
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
        $this->db->set('M_ExpeditionIsActive', 'N')
                ->where('M_ExpeditionID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>
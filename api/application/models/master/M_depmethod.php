<?php

class M_depmethod extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_depmethod";
        $this->table_key = "M_DepMethodID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT *
                FROM `{$this->table_name}`
                WHERE `M_DepMethodName` LIKE ?
                AND `M_DepMethodIsActive` = 'Y'
                LIMIT {$limit} OFFSET {$offset}", [$d['depmethod_name']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_DepMethodName` LIKE ?
            AND `M_DepMethodIsActive` = 'Y'", [$d['depmethod_name']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_dd( $d )
    {
        $r = $this->db->query(
                "SELECT M_DepMethodName depmethod_name, M_DepMethodID depmethod_id, M_DepMethodCode depmethod_code
                FROM `{$this->table_name}`
                WHERE `M_DepMethodName` LIKE ?
                AND `M_DepMethodIsActive` = 'Y'", [$d['depmethod_name']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }
       
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_DepMethodName', $d['depmethod_name'])
                    ->set('M_DepMethodCode', $d['depmethod_code']);
                    // ->set('M_DepMethodUserID', $d['user_id']);
        if (isset($d['depmethod_id']))
        {
            $this->db->where('M_DepMethodID', $d['depmethod_id'])
                ->update( $this->table_name );
            $id = $d['depmethod_id'];
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
        $this->db->set('M_DepMethodIsActive', 'N')
                ->where('M_DepMethodID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>
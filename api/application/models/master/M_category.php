<?php

class M_category extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_category";
        $this->table_key = "M_CategoryID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT M_CategoryID category_id, M_CategoryCode category_code, M_CategoryName category_name
                FROM `{$this->table_name}`
                WHERE `M_CategoryName` LIKE ?
                AND `M_CategoryIsActive` = 'Y'
                ORDER BY M_CategoryCode", [$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_CategoryName` LIKE ?
            AND `M_CategoryIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_CategoryName', $d['category_name'])
                    ->set('M_CategoryCode', $d['category_code']);
                    // ->set('M_CategoryUserID', $d['user_id']);
        if (isset($d['category_id']))
        {
            $this->db->where('M_CategoryID', $d['category_id'])
                ->update( $this->table_name );
            $id = $d['category_id'];
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
        $this->db->set('M_CategoryIsActive', 'N')
                ->where('M_CategoryID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>
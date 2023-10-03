<?php

class M_tag extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_tag";
        $this->table_key = "M_TagID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT M_TagID tag_id, M_TagName tag_name, M_TagType tag_type
                FROM `{$this->table_name}`
                WHERE `M_TagName` LIKE ?
                AND `M_TagIsActive` = 'Y'
                ORDER BY M_TagID", [$d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_TagName` LIKE ?
            AND `M_TagIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function del ($id)
    {
        $this->db->set('M_TagIsActive', 'N')
                ->where('M_TagID', $id)
                ->update($this->table_name);

        return true;
    }
}

?>
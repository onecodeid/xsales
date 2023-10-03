<?php

class M_leadcategory extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_leadcategory";
        $this->table_key = "M_LeadCategoryID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT M_LeadCategoryID category_id, M_LeadCategoryCode category_code, M_LeadCategoryAcronym category_acronym, M_LeadCategoryName category_name
                FROM `{$this->table_name}`
                WHERE `M_LeadCategoryName` LIKE ?
                AND `M_LeadCategoryIsActive` = 'Y'
                ORDER BY M_LeadCategoryCode", [$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_LeadCategoryName` LIKE ?
            AND `M_LeadCategoryIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_LeadCategoryName', $d['category_name'])
                    ->set('M_LeadCategoryCode', $d['category_code'])
                    ->set('M_LeadCategoryAcronym', $d['category_acronym']);
                    // ->set('M_LeadCategoryUserID', $d['user_id']);
        if (isset($d['category_id']))
        {
            $this->db->where('M_LeadCategoryID', $d['category_id'])
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
        $this->db->set('M_LeadCategoryIsActive', 'N')
                ->where('M_LeadCategoryID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>
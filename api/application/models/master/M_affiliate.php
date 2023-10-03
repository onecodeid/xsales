<?php

class M_affiliate extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_affiliate";
        $this->table_key = "M_AffiliateID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT M_AffiliateID affiliate_id, M_AffiliateName affiliate_name, M_AffiliateNumber affiliate_number
                FROM `{$this->table_name}`
                WHERE `M_AffiliateName` LIKE ?
                AND `M_AffiliateIsActive` = 'Y'
                ORDER BY M_AffiliateID", [$d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_AffiliateName` LIKE ?
            AND `M_AffiliateIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_AffiliateName', $d['affiliate_name'])
                    ->set('M_AffiliateNumber', $d['affiliate_number']);
                    // ->set('M_AffiliateUserID', $d['user_id']);
        if (isset($d['affiliate_id']))
        {
            $this->db->where('M_AffiliateID', $d['affiliate_id'])
                ->update( $this->table_name );
            $id = $d['affiliate_id'];
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
        $this->db->set('M_AffiliateIsActive', 'N')
                ->where('M_AffiliateID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>
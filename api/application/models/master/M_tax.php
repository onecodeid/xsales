<?php

class M_tax extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_tax";
        $this->table_key = "M_TaxID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT M_TaxID tax_id, M_TaxName tax_name, M_TaxAmount tax_amount,
                    M_TaxNote tax_note
                FROM `{$this->table_name}`
                WHERE `M_TaxName` LIKE ?
                AND `M_TaxIsActive` = 'Y'
                ORDER BY M_TaxID", [$d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_TaxName` LIKE ?
            AND `M_TaxIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function del ($id)
    {
        $this->db->set('M_TaxIsActive', 'N')
                ->where('M_TaxID', $id)
                ->update($this->table_name);

        return true;
    }
}

?>
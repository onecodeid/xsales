<?php

/**
 * undocumented class
 */
class I_opname extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "i_opname";
        $this->table_key = "I_OpnameID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT i_opname.*
                FROM `{$this->table_name}`
                WHERE (`I_OpnameNumber` LIKE ?)
                AND I_OpnameDate BETWEEN ? AND ?
                AND `I_OpnameIsActive` = 'Y'", [$d['search'], $d['sdate'], $d['edate']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE (`I_OpnameNumber` LIKE ?)
                AND I_OpnameDate BETWEEN ? AND ?
                AND `I_OpnameIsActive` = 'Y'", [$d['search'], $d['sdate'], $d['edate']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save( $d )
    {
        $r = $this->db->query("CALL sp_inventory_opname_save(?, ?, ?)", [$d['hdata'], $d['jdata'], $d['uid']])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    } 
}

?>
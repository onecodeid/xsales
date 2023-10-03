<?php

class T_journalclose extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "t_journalclose";
        $this->table_key = "T_JournalCloseID";
    }

    // SET Laba Rugi
    function Set_profit_loss($date, $amount)
    {
        $r = $this->db->query("CALL `sp_journal_close_set2`(?,?,?)", ['PROFITLOSS.M', $date, $amount])
                ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    // SET Laba Rugi
    // function Set_profit_loss($date, $amount)
    // {
    //     $r = $this->db->query("CALL `sp_journal_close_set`(?,?,?)", ['PROFITLOSS', $date, $amount])
    //             ->row();
    //     $this->clean_mysqli_connection($this->db->conn_id);

    //     return $r;
    // }

    // GET Laba Rugi
    function Get_profit_loss($date)
    {
        $r = $this->db->query("SELECT fn_journal_close_get(?,?) x", ['PROFITLOSS', $date])->row();
        if ($r)
            return json_decode($r->x);
        return json_decode(json_encode([]));
    }
}

?>
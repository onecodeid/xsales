<?php

class T_balance extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "t_balance";
        $this->table_key = "T_BalanceID";
    }

    function set ( $d )
    {
        $r = $this->db->query("CALL sp_balance_set(?,?,?)", [
                        $d['account_id'], 
                        $this->balance_year,
                        $d['jdata']
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }
}

?>
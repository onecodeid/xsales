<?php

class T_journalyearly extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "t_journalyearly";
        $this->table_key = "T_JournalYearlyID";
    }

    // SET
    function Set($year, $month, $data)
    {
        $r = $this->db->query("CALL sp_finance_journal_yearly_set(?, ?, ?)", [$year, $month, json_encode($data)])->row();
        return $r;
    }

    // GET Laba Rugi
    function Get($year, $month)
    {
        $r = $this->db->query("SELECT T_JournalYearlyYear journal_year,
                                T_JournalYearlyBalanceStart balance_start, T_JournalYearlyBalanceEnd balance_end,
                                T_JournalYearlyProfit profit
                                FROM t_journalyearly WHERE T_JournalYearlyYear = ? aND T_JournalYearlyMonth = ? AND T_JournalYearlyIsActive = 'Y'
                                ", [$year, $month])->row();
        return $r;
    }
}

?>
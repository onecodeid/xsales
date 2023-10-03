<?php

class T_journaldetail extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "t_journaldetail";
        $this->table_key = "T_JournalDetailID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT T_JournalDate journal_date, T_JournalNumber	journal_number,
                    T_JournalDebit journal_debit, T_JournalCredit journal_credit,
                    T_JournalNote journal_note
                FROM `{$this->table_name}`
                WHERE (`T_JournalNumber` LIKE ? OR `T_JournalNote` LIKE ?)
                AND `T_JournalIsActive` = 'Y'
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
                WHERE (`T_JournalNumber` LIKE ? OR `T_JournalNote` LIKE ?)
                AND `T_JournalIsActive` = 'Y'", [$d['search'], $d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }
}

?>
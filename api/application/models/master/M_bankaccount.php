<?php

class M_bankaccount extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_bankaccount";
        $this->table_key = "M_BankAccountID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT M_BankAccountID account_id, M_BankAccountName account_name, M_BankAccountNumber account_number, M_BankName bank_name,
                    M_BankAccountPPN account_ppn, M_BankAccountM_AccountID account_idx
                FROM `{$this->table_name}`
                JOIN m_bank ON M_BankAccountM_BankID = M_BankID
                WHERE (`M_BankAccountName` LIKE ? OR `M_BankAccountNumber` LIKE ? OR `M_BankName` LIKE ?)
                AND `M_BankAccountIsActive` = 'Y'
                LIMIT {$limit} OFFSET {$offset}", [$d['search'],$d['search'],$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            JOIN m_bank ON M_BankAccountM_BankID = M_BankID
            WHERE (`M_BankAccountName` LIKE ? OR `M_BankAccountNumber` LIKE ? OR `M_BankName` LIKE ?)
            AND `M_BankAccountIsActive` = 'Y'", [$d['search'],$d['search'],$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_autocomplete( $d )
    {
        $d['limit'] = 250;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function get($id)
    {
        $r = $this->db->select('M_BankID bank_id, M_BankName bank_name, M_BankAccountID account_id, M_BankAccountName account_name, M_BankAccountNumber account_number', false)
                    ->join('m_bank', 'M_BankAccountM_BankID = M_BankID')
                    ->where($this->table_key, $id)
                    ->get($this->table_name)
                    ->row();

        return $r;
    }
}

?>
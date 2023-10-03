<?php

class T_journal extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "t_journal";
        $this->table_key = "T_JournalID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $filter = '';
        if (isset($d['filter']))
        {
            if (!is_array($d['filter']))
                $filter = "AND T_JournalMainM_AccountID = {$d['filter']}";
            else if (is_array($d['filter']) && sizeof($d['filter']) == 1)
                $filter = "AND T_JournalMainM_AccountID = {$d['filter'][0]}";
            else
                $filter = "AND FIND_IN_SET(T_JournalMainM_AccountID, '".join(',', $d['filter'])."')";
        }

        $jtype = '';
        if (isset($d['jtype']))
        {
            if (!is_array($d['jtype']))
                $filter = "AND T_JournalType = '{$d['jtype']}'";
            else if (is_array($d['jtype']) && sizeof($d['jtype']) == 1)
                $filter = "AND T_JournalMainM_AccountID = '{$d['jtype'][0]}'";
            else
                $filter = "AND FIND_IN_SET(T_JournalMainM_AccountID, '".join(',', $d['jtype'])."')";
        }

        $journal_id = isset($d['journal_id']) ? $d['journal_id'] : 0;
        // $filter = isset($d['filter'])?"AND FIND_IN_SET(T_JournalMainM_AccountID, '".join(',', $d['filter'])."')":"";

        $r = $this->db->query(
                "SELECT T_JournalID journal_id, T_JournalDate journal_date, T_JournalNumber	journal_number, T_JournalReceipt journal_receipt,
                    T_JournalDebit journal_debit, T_JournalCredit journal_credit,
                    T_JournalNote journal_note, T_JournalPost journal_post, T_JournalSubType journal_subtype, T_JournalRefID journal_refid,
                    T_JournalRefNote journal_refnote,
                    CONCAT('[',GROUP_CONCAT(JSON_OBJECT('account', T_JournalDetailM_AccountID, 'debit', T_JournalDetailDebit, 'credit', T_JournalDetailCredit, 'post', T_JournalDetailPost,
                        'accountx', JSON_OBJECT('account_id', M_AccountID, 'account_code', M_AccountCode, 'account_name', M_AccountName) ) 
                        ORDER BY T_JournalDetailID SEPARATOR ','), ']') details,
                    CONCAT('[',GROUP_CONCAT(CONCAT('\"', M_AccountName, '\"') SEPARATOR ','), ']') accounts,
                    M_JournalTypeName journaltype_name, T_JournalType journal_type,
                    IFNULL(T_JournalTags, '[]') journal_tags
                FROM `{$this->table_name}`
                LEFT JOIN t_journaldetail ON T_JournalID = T_JournalDetailT_JournalID
                    AND T_JournalDetailIsActive = 'Y'
                LEFT JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
                LEFT JOIN m_journaltype ON T_JournalType = M_JournalTypeCode AND M_JournalTypeIsActive = 'Y'
                WHERE (`T_JournalNumber` LIKE ? OR `T_JournalNote` LIKE ? OR `T_JournalRefNote` LIKE ?)
                AND `T_JournalIsActive` = 'Y'
                AND T_JournalDate BETWEEN ? AND ?
                AND ((T_JournalID = ? AND ? <> 0) OR ? = 0)
                {$filter} {$jtype}
                GROUP BY T_JournalID
                ORDER BY T_JournalNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate'])),
                    $journal_id, $journal_id, $journal_id]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['details'] = json_decode($v['details']);
                $r[$k]['accounts'] = json_decode($v['accounts']);
                $r[$k]['journal_tags'] = json_decode($v['journal_tags']);
            }
                
            $l['records'] = $r;
            $l['query'] = $this->db->last_query();
        }

        $r = $this->db->query(
            "SELECT count(distinct `{$this->table_key}`) n
            FROM `{$this->table_name}`
                WHERE (`T_JournalNumber` LIKE ? OR `T_JournalNote` LIKE ? OR `T_JournalRefNote` LIKE ?)
                AND `T_JournalIsActive` = 'Y'
                {$filter}
                AND T_JournalDate BETWEEN ? AND ?
                AND ((T_JournalID = ? AND ? <> 0) OR ? = 0)", [$d['search'], $d['search'], $d['search'], date('Y-m-d', strtotime($d['sdate'])), date('Y-m-d', strtotime($d['edate'])),
                    $journal_id, $journal_id, $journal_id]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_id ($id)
    {
        $r = $this->search(['page'=>1,'search'=>'%','journal_id'=>$id,'sdate'=>'1970-01-01','edate'=>date('Y-m-d')]);
        return isset($r['records'][0])?$r['records'][0]:null;
    }

    function search_cashbank ( $d )
    {
        $d['filter'] = [$d['account_id']];
        return $this->search( $d );
    }

    function save ( $d, $id = 0 )
    {
        $r = $this->db->query("CALL sp_journal_save_tags(?,?,?,?,?,?)", [
                        $id, 
                        date('Y-m-d', strtotime($d['journal_date'])),
                        $d['journal_receipt'],
                        $d['journal_note'],
                        $d['jdata'],
                        isset($d['journal_tags'])?$d['journal_tags']:'[]'
                    ])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function del ($id)
    {
        $r = $this->db->query("CALL sp_journal_delete(?)", [$id])
                        ->row();

        return $r;
    }

    function post ($id)
    {
        $r = $this->db->query("CALL sp_journal_post(?)", [$id])
                        ->row();

        return $r;
    }

    function cash_receive ($d, $id = 0)
    {
        $r = $this->db->query("CALL sp_cash_receive(?,?,?,?,?,?,?)", [
            $id,
            date('Y-m-d', strtotime($d['journal_date'])),
            $d['journal_receipt'],
            $d['journal_note'],
            $d['journal_type'],
            $d['jdata'],
            $d['account_id']
        ])
        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function cash_receive2 ($d, $id = 0)
    {
        $r = $this->db->query("CALL sp_cash_receive2(?,?,?)", [
            $id, 
            $d['hdata'],
            $d['jdata']
        ])
        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function cash_delete ($jid, $uid)
    {
        $r = $this->db->query("CALL sp_cash_delete(?,?)", [
            $jid, 
            $uid
        ])
        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        if ($r->status == "OK")
            $r->data = json_decode($r->data);
        return $r;
    }

    function get_detail ($jid)
    {
        $r = $this->db->query("SELECT T_JournalDetailID id, round(T_JournalDetailDebit) debit, round(T_journalDetailCredit) credit, 
                T_JournalDetailDebit debit_real, T_journalDetailCredit credit_real,
                JSON_OBJECT('account_id', M_AccountID, 'account_code', M_AccountCode, 'account_name', M_accountName) account
                FROM t_journaldetail
                JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
                WHERE T_JournalDetailT_JournalID = ?
                AND (T_JournalDetailIsActive = 'Y' Or T_JournalDetailIsActive = 'T')
                ORDER BY T_JournalDetailID", [$jid]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
                $r[$k]['account'] = json_decode($v['account']);

            return json_decode(json_encode(['status'=>'OK', 'data'=>$r]));
        }

        return json_decode(json_encode(['status'=>'ERR', 'message'=>[]]));
    }

    function search_by_account($d)
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $search = $d['search'];
        $sdate = date('Y-m-d', strtotime($d['sdate']));
        $edate = date('Y-m-d', strtotime($d['edate']));
        $account_id = $d['account_id'];

        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];


        $q = "SELECT  T_JournalID journal_id, T_JournalDate journal_date, T_JournalNumber	journal_number, T_JournalReceipt journal_receipt,
                    T_JournalDebit journal_debit, T_JournalCredit journal_credit,
                    T_JournalNote journal_note, T_JournalPost journal_post, T_JournalSubType journal_subtype, T_JournalRefID journal_refid,
                    T_JournalRefNote journal_refnote,
                    CONCAT('[',GROUP_CONCAT(JSON_OBJECT('account', T_JournalDetailM_AccountID, 'debit', T_JournalDetailDebit, 'credit', T_JournalDetailCredit, 'post', T_JournalDetailPost,
                        'accountx', JSON_OBJECT('account_id', M_AccountID, 'account_code', M_AccountCode, 'account_name', M_AccountName) ) 
                        ORDER BY T_JournalDetailID SEPARATOR ','), ']') details,
                    CONCAT('[',GROUP_CONCAT(CONCAT('\"', M_AccountName, '\"') SEPARATOR ','), ']') accounts,
                    M_JournalTypeName journaltype_name, T_JournalType journal_type,
                    IFNULL(T_JournalTags, '[]') journal_tags,

                    jdebit, jcredit, IFNULL(ledgernote, T_JournalNote) ledgernote,
                    IF(jdebit = 0, jcredit, jdebit) nominal, acctype, accname,
                    IF((acctype = 'A' AND jdebit > 0) OR (acctype = 'P' AND jcredit > 0), 'IN', 'OUT') `accflow`
                FROM t_journal
                JOIN (
                    SELECT T_JournalID jid, T_JournalDetailDebit jdebit, T_JournalDetailCredit jcredit,
                        M_AccountType acctype, M_AccountName accname, T_JournalDetailLedgerNote ledgernote
                    FROM t_journaldetail
                    JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID
                        AND T_JournalDate BETWEEN ? AND ? ANd T_JournalIsActive = 'Y'
                    JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
                    WHERE T_JournalDetailIsactive = 'Y' AND T_JournalDetailM_AccountID = ?
                        AND (`T_JournalNumber` LIKE ? OR `T_JournalNote` LIKE ? OR `T_JournalRefNote` LIKE ?)
                    GROUP BY T_JournalID
                ) a ON T_JournalID = jid
                LEFT JOIN t_journaldetail ON T_JournalID = T_JournalDetailT_JournalID AND T_JournalDetailIsActive = 'Y'
                LEFT JOIN m_account ON T_JournalDetailM_AccountID = M_AccountId
                LEFT JOIN m_journaltype ON T_JournalType = M_JournalTypeCode AND M_JournalTypeIsActive = 'Y'
                WHERE T_JournalIsActive = 'Y'
                GROUP BY T_JournalID
                ORDER BY T_JournalDate DESC
                LIMIT {$limit} OFFSET {$offset}";
        $r = $this->db->query($q, [$sdate, $edate, $account_id, $search, $search, $search]);

        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['details'] = json_decode($v['details']);
                $r[$k]['accounts'] = json_decode($v['accounts']);
                $r[$k]['journal_tags'] = json_decode($v['journal_tags']);
            }
                
            $l['records'] = $r;
            // $l['query'] = $this->db->last_query();
        }

        $q = "SELECT COUNT(DISTINCT T_JournalID) n
                FROM t_journaldetail
                JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID
                    AND T_JournalDate BETWEEN ? AND ? ANd T_JournalIsActive = 'Y'
                WHERE T_JournalDetailIsactive = 'Y' AND T_JournalDetailM_AccountID = ?
                    AND (`T_JournalNumber` LIKE ? OR `T_JournalNote` LIKE ? OR `T_JournalRefNote` LIKE ?)
                ";
        $r = $this->db->query($q, [$sdate, $edate, $account_id, $search, $search, $search]);

        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }

        return $l;
    }
}

?>
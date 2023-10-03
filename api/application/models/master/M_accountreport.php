<?php

class M_accountreport extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_accountreport";
        $this->table_key = "M_AccountReportID";
    }

    function search_group()
    {
        $q = "SELECT M_AccountReportTypeGroup id, M_AccountReportTypeLabel label
            FROM m_accountreporttype
            WHERE M_AccountReportTypeIsActive = 'Y'
            GROUP BY M_AccountReportTypeGroup
            ORDER BY M_AccountReportTypeGroup ASC, M_AccountReportTypeSort ASC, M_AccountReportTypeName ASC";
        $r = $this->db->query($q)->result_array();

        return $r;
        // return [
        //     ["id"=>"INCOME", 
        //         "label"=>"RUGI LABA"],
        //     ["id"=>"BALANCE", 
        //         "label"=>"NERACA"],
        //     ["id"=>"CASHFLOW", 
        //         "label"=>"ARUS KAS"],
        //     ["id"=>"MANAGEMENT", 
        //         "label"=>"MANAJEMEN ANGGARAN"]];
    }

    function search2( $d )
    {
        $rst = [];
        $q = "SELECT 
                M_AccountReportTypeName account_type, M_AccountReportTypeSubTitle account_title,
                CONCAT('[', 
                    GROUP_CONCAT(
                        JSON_OBJECT('account_type', M_AccountReportTypeName, 'account_title', M_AccountReportTypeSubTitle, 
                            'account_id', IFNULL(M_AccountID, M_AccountGroupID), 'is_group', IF(M_AccountID IS NULL, 'Y', 'N'),
                            'account', IF(M_AccountID IS NULL, 
                                JSON_OBJECT('account_id', M_AccountGroupID,'account_name',M_AccountGroupName, 'is_group', 'Y'),
                                JSON_OBJECT('account_id', M_AccountID,'account_name', M_AccountName, 'is_group', 'N') ) )
                    ), ']') details
                FROM m_accountreporttype
                LEFT JOIN m_accountreport ON M_AccountReportTypeName = M_AccountReportType AND M_AccountReportIsActive = 'Y'
                LEFT JOIN m_account ON M_AccountReportM_AccountID = M_AccountID AND M_accountReportM_AccountID <> 0
                LEFT JOIN m_accountgroup ON M_AccountReportM_AccountGroupID = M_AccountGroupID AND M_AccountReportM_AccountGroupID <> 0
            
            WHERE M_AccountReportTypeIsActive = 'Y'
            AND M_AccountReportTypeGroup = ?
            GROUP BY M_AccountReportTypeName
            ORDER BY M_AccountReportTypeSort ASC, M_AccountReportTypeSubTitle ASC, M_AccountReportSort ASC, M_AccountName ASC";
        $r = $this->db->query($q, [$d['group']]);
        if ($r)
        {
            $rst = $r->result_array();
            foreach($rst as $k => $v)
            {
                $details = json_decode($v['details']);
                foreach ($details as $l => $w)
                {
                    $details[$l]->account = json_decode($w->account);
                }
                $rst[$k]['details'] = $details;
            }
        }

        return $rst;
    }

    function search( $d )
    {
        $rst = [];
        $q = "SELECT M_AccountReportType account_type, IFNULL(M_AccountReportTitle, '') account_title, 
                IFNULL(M_AccountID, 0) account_id, IFNULL(M_AccountGroupID, 0) accountgroup_id,
                IFNULL(M_ACcountName, '') account_name, IFNULL(M_AccountGroupName, '') accountgroup_name,
                IF(M_AccountGroupID IS NULL, 'N', 'Y') is_group
            FROM m_accountreport
            LEFT JOIN m_account ON M_AccountReportM_AccountID = M_AccountID AND M_accountReportM_AccountID <> 0
            LEFT JOIN m_accountgroup ON M_AccountReportM_AccountGroupID = M_AccountGroupID AND M_AccountReportM_AccountGroupID <> 0
            WHERE M_AccountReportIsActive = 'Y'
            AND M_AccountReportType LIKE ?
            ORDER BY M_AccountReportSort ASC, M_AccountReportType ASC, M_AccountName ASC";
        $r = $this->db->query($q, [$d['group']]);
        if ($r)
        {
            $rst = $r->result_array();
            foreach($rst as $k => $v)
            {
                $rst[$k]['account'] = ['account_id'=>($v['account_id']!=0?$v['account_id']:$v['accountgroup_id']),
                                        'account_name'=>($v['account_name']!=''?$v['account_name']:$v['accountgroup_name']),'is_group'=>$v['is_group']];
            }
        }

        return $rst;
    }

    function save ( $d )
    {
        $r = $this->db->query("CALL sp_master_accountreport_save(?, ?)", [$d['group'], $d['jdata']])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function save_new ( $d, $id = 0 )
    {
        $r = $this->db->query("CALL sp_master_account_save_new(?, ?, ?)", [$d['hdata'], $id, $d['user_id']])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        // $r->data = $this->db->last_query();
        return $r;
    }

    function del ($id, $uid = 1)
    {
        $r = $this->db->query("CALL sp_master_account_delete(?, ?)", [$id, $uid])->row();
        return $r;
    }
}

?>
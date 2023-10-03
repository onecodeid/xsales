<?php

class M_account extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_account";
        $this->table_key = "M_AccountID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $group = isset($d['group_id'])?$d['group_id']:0;
        $side = isset($d['side'])?$d['side']:'';
        
        $level = isset($d['level'])? $d['level'] :0;
        $level_len = [0=>3, 1=>5, 2=>7];

        $isparent = isset($d['is_parent'])?$d['is_parent']:'';
        $isused = isset($d['isused'])? $d['isused'] : '';

        $addon_where = isset($d['addon_where']) ? $d['addon_where'] : '';

        $groups = "";
        if (isset($d['groups']))
        {
            $groups = "a.M_AccountM_AccountGroupID IN ({$d['groups']})";
        }

        $r = $this->db->query(
                "SELECT a.*, m_accountgroup.*, IFNULL(T_AccountLastDate, '') last_date,
                    IFNULL(T_AccountLastBalance, 0) last_balance,
                    IFNULL(M_AccountGroupName, '') group_name,
                    IFNULL(b.M_AccountCode, '') parent_code,
                    IF(b.M_AccountCode IS NULL, 0, 1) level,
                    IFNULL(pay.M_AccountMapReffID, 0) pay_id,
                    IFNULL(pay.M_AccountMapA_BankAccountID, 0) bank_account_id,
                    false parent,
                    IFNULL(T_BalanceID, 0) balance_id,
                    IFNULL(T_BalanceOpenCredit, 0) balance_open_credit,
                    IFNULL(T_BalanceOpenDebit, 0) balance_open_debit,
                    IFNULL(M_AccountMapType, '') map_type,
                    IFNULL(M_AccountMapReffID, 0) map_ref,

                    a.M_AccountID account_id, a.M_AccountCode account_code, a.M_AccountName account_name,
                    a.M_AccountCredit balance_credit,
                    a.M_AccountDebit balance_debit,
                    a.M_AccountLastCredit last_credit,
                    a.M_AccountLastDebit last_debit,
                    a.M_AccountType account_side,
                    a.M_AccountPos account_pos
                FROM `{$this->table_name}` a
                LEFT JOIN t_accountlast ON T_AccountLastM_AccountID = a.M_AccountID
                    AND T_AccountLastIsActive = 'Y'
                LEFT JOIN m_accountgroup ON a.M_AccountM_AccountGroupID =M_AccountGroupID
                LEFT JOIN m_account b ON b.M_AccountIsActive = 'Y' AND a.M_AccountCode LIKE CONCAT(b.M_AccountCode,'%') AND b.M_AccountCode <> a.M_AccountCode
                LEFT JOIN m_accountmap pay ON M_AccountMapIsActive = 'Y' AND M_AccountMapType LIKE 'PAY%' AND M_AccountMapM_AccountID = a.M_AccountID
                LEFT JOIN t_balance ON T_BalanceM_AccountID = a.M_AccountID ANd T_BalanceIsActive = 'Y' AND T_BalanceYear = ?
                WHERE a.`M_AccountName` LIKE ?
                AND a.`M_AccountIsActive` = 'Y'
                AND ((a.`M_AccountM_AccountGroupID`= ? AND ? > 0) OR (? = 0))
                AND ((a.M_AccountType = ? AND ? <> '') OR ? = '')
                AND ((length(a.M_AccountCode) = ? AND ? <> 0) OR ? = 0)
                AND ((a.M_AccountParent = ? AND ? <> '') OR ? = '')
                AND ((a.M_AccountUsed = ? AND ? <> '') OR ? = '')
                {$addon_where}
                {$groups}
                ORDER BY a.M_AccountCode
                LIMIT {$limit} OFFSET {$offset}", [$this->balance_year, $d['account_name'], $group, $group, $group, $side, $side, $side,
                    $level_len[$level], $level, $level, 
                    $isparent, $isparent, $isparent,
                    $isused, $isused, $isused]);
        if ($r)
        {
            $this->load->model('master/m_bankaccount');

            $x = $r->result_array();
            $parents = [];
            foreach ($x as $k => $v)
            {
                if ($v['parent_code'] != "" && array_search($v['parent_code'], $parents) === false)
                    $parents[] = $v['parent_code'];

                $x[$k]['ref_note'] = '';
                if ($v['map_type'] == 'PAY.02')
                {
                    $y = $this->m_bankaccount->get($v['bank_account_id']);
                    if ($y)
                        $x[$k]['ref_note'] = $y;
                }
            }
                

            foreach ($x as $k => $v)
                if (array_search($v['M_AccountCode'], $parents) !== false)
                    $x[$k]['parent'] = true;

            $l['records'] = $x;
            $l['parents'] = $parents;
        }

        $groups = preg_replace("/(a\.)/", "", $groups);
        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}` a
            WHERE `M_AccountName` LIKE ?
            AND `M_AccountIsActive` = 'Y'
            AND ((`M_AccountM_AccountGroupID`= ? AND ? > 0) OR (? = 0))
            AND ((M_AccountType = ? AND ? <> '') OR ? = '')
            AND ((length(M_AccountCode) = ? AND ? <> 0) OR ? = 0)
            AND ((M_AccountParent = ? AND ? <> '') OR ? = '')
            AND ((M_AccountUsed = ? AND ? <> '') OR ? = '')
            {$addon_where}
            {$groups}", [$d['account_name'], $group, $group, $group, $side, $side, $side,
                $level_len[$level], $level, $level, 
                $isparent, $isparent, $isparent,
                $isused, $isused, $isused]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
            $l['group'] = $group;
        }
            
        return $l;
    }

    function search_dd( $d )
    {
        $group = isset($d['group_id'])?$d['group_id']:0;

        $r = $this->db->query(
                "SELECT a.M_AccountName account_name, a.M_AccountID account_id, a.M_AccountCode account_code, IFNULL(b.M_AccountCode, '') parent_code
                FROM `{$this->table_name}` a
                LEFT JOIN m_account b ON b.M_AccountIsActive = 'Y' AND a.M_AccountCode LIKE CONCAT(b.M_AccountCode,'%') AND b.M_AccountCode <> a.M_AccountCode
                WHERE a.`M_AccountName` LIKE ?
                AND a.`M_AccountIsActive` = 'Y'
                AND ((a.`M_AccountM_AccountGroupID`= ? AND ? > 0) OR (? = 0))
                ORDER BY a.M_AccountCode", [$d['account_name'], $group, $group, $group]);
        if ($r)
        {
            $x = $r->result_array();
            $parents = [];
            foreach ($x as $k => $v)
                if ($v['parent_code'] != "" && array_search($v['parent_code'], $parents) === false)
                    $parents[] = $v['parent_code'];

            foreach ($x as $k => $v)
                if (array_search($v['account_code'], $parents) !== false)
                    $x[$k]['parent'] = true;

            $l['records'] = $x;
        }
       
        return $l;
    }

    function save ( $d, $id = 0 )
    {
        $hdata = json_encode([
            'code' => $d['account_code'],
            'prefix' => $d['account_prefix'],
            'name' => $d['account_name'],
            'group' => $d['account_group'],
            'parent' => $d['account_parent'],
            'from' => $d['account_from'],
            'pos' => $d['account_pos']
        ]);
        $r = $this->db->query("CALL sp_master_account_save(?, ?, ?)", [$id, $hdata, $d['user_id']])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;

        $r = $this->db->set('M_AccountName', $d['account_name'])
                    ->set('M_AccountCode', $d['account_code'])
                    ->set('M_AccountM_AccountGroupID', $d['account_group']);
                    
                    // ->set('M_AccountUserID', $d['user_id']);
        if (isset($d['account_id']))
        {
            $this->db->where('M_AccountID', $d['account_id'])
                ->update( $this->table_name );
            $id = $d['account_id'];
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
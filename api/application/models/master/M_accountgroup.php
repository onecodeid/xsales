<?php

class M_accountgroup extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_accountgroup";
        $this->table_key = "M_AccountGroupID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT M_AccountGroupID group_id, M_AccountGroupCode group_code, M_AccountGroupName group_name
                FROM `{$this->table_name}` a
                WHERE (`M_AccountGroupName` LIKE ? OR M_AccountGroupCode LIKE ?)
                AND `M_AccountGroupIsActive` = 'Y'
                ORDER BY M_AccountGroupCode ASC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }
        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE (`M_AccountGroupName` LIKE ? OR M_AccountGroupCode LIKE ?)
                AND `M_AccountGroupIsActive` = 'Y'", [$d['search'], $d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    // function save ( $d )
    // {
    //     $r = $this->db->set('M_AccountName', $d['account_name'])
    //                 ->set('M_AccountCode', $d['account_code']);
    //                 // ->set('M_AccountUserID', $d['user_id']);
    //     if (isset($d['account_id']))
    //     {
    //         $this->db->where('M_AccountID', $d['account_id'])
    //             ->update( $this->table_name );
    //         $id = $d['account_id'];
    //     }
    //     else
    //     {
    //         $this->db->insert( $this->table_name );
    //         $id = $this->db->insert_id();
    //     }

    //     if ($r)
    //     {
    //         return (object) ["status"=>"OK", "data"=>$id];
    //     }

    //     return (object) ["status"=>"ERR"];
    // }

    // function del ($id)
    // {
    //     $this->db->set('M_AccountIsActive', 'N')
    //             ->where('M_AccountID', $this->sys_input['id'])
    //             ->update($this->table_name);

    //     return true;
    // }
}

?>
<?php

class M_expense extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_expense";
        $this->table_key = "M_ExpenseID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT *
                FROM `{$this->table_name}`
                WHERE `M_ExpenseName` LIKE ?
                AND `M_ExpenseIsActive` = 'Y'
                LIMIT {$limit} OFFSET {$offset}", [$d['expense_name']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_ExpenseName` LIKE ?
            AND `M_ExpenseIsActive` = 'Y'", [$d['expense_name']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_ExpenseName', $d['expense_name'])
                    ->set('M_ExpenseCode', $d['expense_code']);
                    // ->set('M_ExpenseUserID', $d['user_id']);
        if (isset($d['expense_id']))
        {
            $this->db->where('M_ExpenseID', $d['expense_id'])
                ->update( $this->table_name );
            $id = $d['expense_id'];
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

    function del ($id)
    {
        $this->db->set('M_ExpenseIsActive', 'N')
                ->where('M_ExpenseID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>
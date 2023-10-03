<?php

class M_itemalias extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_itemalias";
        $this->table_key = "M_ItemAliasID";
    }

    function search( $d )
    {
        // $dberp = $this->load->database('dberp', true);
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $item = isset($d['item'])?$d['item']:0;

        $r = $this->db->query(
                "SELECT M_ItemAliasID alias_id, M_ItemAliasName alias_name, M_ItemAliasName item_alias, 
                M_CustomerID customer_id, M_CustomerName customer_name
                FROM `{$this->table_name}`
                JOIN m_customer ON M_ItemAliasM_CustomerID = M_CustomerID
                WHERE `M_ItemAliasName` LIKE ?
                AND M_ItemAliasIsActive = 'Y'
                AND ((M_ItemAliasM_ItemID = ? ANd ? <> 0) OR ? = 0)
                LIMIT {$limit} OFFSET {$offset}", [$d['item_name'], $item, $item, $item]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
                JOIN m_customer ON M_ItemAliasM_CustomerID = M_CustomerID
            WHERE `M_ItemAliasName` LIKE ?
            AND M_ItemAliasIsActive = 'Y'
            AND ((M_ItemAliasM_ItemID = ? ANd ? <> 0) OR ? = 0)", [$d['item_name'], $item, $item, $item]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    // function save ( $d )
    // {
    //     $r = $this->db->set('M_ItemName', $d['item_name'])
    //                 ->set('M_ItemCode', $d['item_code']);
    //                 // ->set('M_VendorUserID', $d['user_id']);
    //     if (isset($d['item_id']))
    //     {
    //         $this->db->where('M_ItemID', $d['item_id'])
    //             ->update( $this->table_name );
    //         $id = $d['item_id'];
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
    //     $this->db->set('M_ItemIsActive', 'N')
    //             ->where('M_ItemID', $this->sys_input['id'])
    //             ->update($this->table_name);

    //     return true;
    // }
}

?>
<?php

class M_tmp_sales_import_customer extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "_tmp_sales_import_customer";
        $this->table_key = "id";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT id as id, a as a, b as b, c as c,
                    IF(c = 0, null, JSON_OBJECT('customer_id', c, 'customer_name', b)) d
                FROM `{$this->table_name}`
                LEFT JOIN m_customer ON M_CustomerID = c
                WHERE `a` LIKE ?
                AND ((c = 0 AND ? = 'Y') OR (c > -1 && ? = 'N'))
                ORDER BY a asc
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $d['unmap'], $d['unmap']]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['d']= $v['d'] == null ? json_decode(json_encode([])) : json_decode($v['d']);
            }
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `a` LIKE ?
            AND ((c = 0 AND ? = 'Y') OR (c > -1 && ? = 'N'))", [$d['search'], $d['unmap'], $d['unmap']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function save($d)
    {
        $r = $this->db->set('b', $d['b'])
                        ->set('c', $d['c'])
                        ->where('id', $d['id'])
                        ->update( $this->table_name );

        return (object) ["status"=>"OK", "data"=>$d['id']];
    }

    // function save ( $d )
    // {
    //     $r = $this->db->set('M_ExpeditionName', $d['expedition_name'])
    //                 ->set('M_ExpeditionCode', isset($d['expedition_code'])?$d['expedition_code']:'')
    //                 ->set('M_ExpeditionWebsite', isset($d['expedition_website'])?$d['expedition_website']:'')
    //                 ->set('M_ExpeditionAddress', isset($d['expedition_address'])?$d['expedition_address']:'')
    //                 ->set('M_ExpeditionPhone1', isset($d['expedition_phone1'])?$d['expedition_phone1']:'')
    //                 ->set('M_ExpeditionPhone2', isset($d['expedition_phone2'])?$d['expedition_phone2']:'')
    //                 ->set('M_ExpeditionDestination', isset($d['expedition_destination'])?$d['expedition_destination']:'')
    //                 ->set('M_ExpeditionNote', isset($d['expedition_note'])?$d['expedition_note']:'');
    //                 // ->set('M_ExpeditionUserID', $d['user_id']);
    //     if (isset($d['expedition_id']))
    //     {
    //         $this->db->where('M_ExpeditionID', $d['expedition_id'])
    //             ->update( $this->table_name );
    //         $id = $d['expedition_id'];
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
    //     $this->db->set('M_ExpeditionIsActive', 'N')
    //             ->where('M_ExpeditionID', $this->sys_input['id'])
    //             ->update($this->table_name);

    //     return true;
    // }
}

?>
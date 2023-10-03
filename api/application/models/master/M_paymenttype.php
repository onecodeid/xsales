<?php

class M_paymenttype extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_paymenttype";
        $this->table_key = "M_PaymentTypeID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT M_PaymentTypeID paymenttype_id, M_PaymentTypeName paymenttype_name, M_PaymentTypeCode paymenttype_code
                FROM `{$this->table_name}`
                WHERE `M_PaymentTypeName` LIKE ?
                AND `M_PaymentTypeIsActive` = 'Y'
                LIMIT {$limit} OFFSET {$offset}", [$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_PaymentTypeName` LIKE ?
            AND `M_PaymentTypeIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function search_autocomplete( $d )
    {
        $d['limit'] = 25;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }
}

?>
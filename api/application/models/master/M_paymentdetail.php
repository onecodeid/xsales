<?php

class M_paymentdetail extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_paymentdetail";
        $this->table_key = "M_PaymentDetailID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT M_PaymentDetailID paymentdetail_id, M_PaymentDetailName paymentdetail_name, M_PaymentDetailCode paymentdetail_code, M_PaymentDetailIsDisc is_disc, M_PaymentDetailIsRetur is_retur
                FROM `{$this->table_name}`
                WHERE `M_PaymentDetailName` LIKE ?
                AND `M_PaymentDetailIsActive` = 'Y'
                LIMIT {$limit} OFFSET {$offset}", [$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_PaymentDetailName` LIKE ?
            AND `M_PaymentDetailIsActive` = 'Y'", [$d['search']]);
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
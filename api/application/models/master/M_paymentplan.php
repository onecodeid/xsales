<?php

class M_paymentplan extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_paymentplan";
        $this->table_key = "M_PaymentPlanID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT M_PaymentPlanID paymentplan_id, M_PaymentPlanName paymentplan_name
                FROM `{$this->table_name}`
                WHERE `M_PaymentPlanName` LIKE ?
                AND `M_PaymentPlanIsActive` = 'Y'
                ORDER BY M_PaymentPlanCode ASC
                LIMIT {$limit} OFFSET {$offset}
                ", [$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_PaymentPlanName` LIKE ?
            AND `M_PaymentPlanIsActive` = 'Y'", [$d['search']]);
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
}

?>
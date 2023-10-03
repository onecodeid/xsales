<?php

class M_journaltype extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_journaltype";
        $this->table_key = "M_JournalTypeID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT M_JournalTypeID journaltype_id, M_JournalTypeCode journaltype_code, M_JournalTypeName journaltype_name
                FROM `{$this->table_name}`
                WHERE (`M_JournalTypeName` LIKE ? OR `M_JournalTypeCode` LIKE ?)
                AND `M_JournalTypeIsActive` = 'Y'
                LIMIT {$limit} OFFSET {$offset}", [$d['search'],$d['search']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE (`M_JournalTypeName` LIKE ? OR `M_JournalTypeCode` LIKE ?)
            AND `M_JournalTypeIsActive` = 'Y'", [$d['search'],$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    // function search_autocomplete( $d )
    // {
    //     $d['limit'] = 25;
    //     $d['page'] = 1;
    //     $r = $this->search( $d );
        
    //     return $r['records'];
    // }
}

?>
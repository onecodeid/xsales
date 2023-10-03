<?php

class Log_activity extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "log_activity";
        $this->table_key = "Log_ActivityID";
    }

    function search ( $d )
    {
        $sdate = isset($d['sdate'])?date("Y-m-d", strtotime($d['sdate'])):'2021-01-01';
        $edate = isset($d['edate'])?date("Y-m-d", strtotime($d['edate'])):date("Y-m-d");

        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;

        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $dir = getcwd()."/application/models/report/sql/";
        $q = file_get_contents($dir."q_log001.sql");
        $qq = explode('###', $q);
        $r = $this->db->query($qq[0], [$sdate, $edate, $d['search'], $d['search'], $d['search'], $limit, $offset])->result_array();

        $l['records'] = $r;

        $r = $this->db->query($qq[1], [$sdate, $edate, $d['search'], $d['search'], $d['search']])->row();
        $l['total'] = $r->n;
        $l['total_page'] = ceil($r->n / $limit);

        return $l;
    }

    function set( $d )
    {
        $this->db->query("CALL sp_log_activity(?, ?, ?)", []);
        $this->clean_mysqli_connection($this->db->conn_id);

        return true;
    }
}

?>
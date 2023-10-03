<?php
class D_dashboard extends MY_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function get_customer_total_by_admin ($uid)
    {
        $r = $this->db->query("CALL sp_dashboard_stat_customer(?)", [$uid])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    function get_customer_total_new ($uid)
    {
        $r = $this->db->query("CALL sp_stat_master_001()")
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    function get_fee_total_by_admin ($uid, $sdate, $edate)
    {
        $r = $this->db->query("CALL sp_dashboard_admin_fee_total(?, ?, ?)", [$uid, $sdate, $edate])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    function get_omzet_by_product ($uid, $sdate, $edate, $type = 'A')
    {
        $r = $this->db->query("CALL sp_dashboard_omzet_by_product(?, ?, ?, ?)", [$uid, $sdate, $edate, $type])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    function get_target_this_week ($uid)
    {
        $r = $this->db->query("CALL sp_stat_sales_004()")
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }

    function get_omzet_admin ($uid)
    {
        $r = $this->db->query("CALL sp_stat_sales_006(?)", $uid)
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);
        return $r;
    }
}
?>


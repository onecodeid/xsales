<?php

class S_notif extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = 's_notif';
        $this->primary_key = 'S_NotifID';
    }

    function get_unread($uid, $seller = "N")
    {
        $r = $this->db->query("CALL sp_system_notif_unread(?, ?)", [$uid, $seller])
                        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);        

        return json_decode($r->data);
    }

    function set_read($uid, $seller = "N")
    {
        $r = $this->db->query("CALL sp_system_notif_read(?, ?)", [$uid, $seller])
                        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);        

        return json_decode($r->data);
    }
}
?>

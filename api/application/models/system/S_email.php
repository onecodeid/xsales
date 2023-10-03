<?php

class S_email extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = 's_email';
        $this->primary_key = 'S_EmailID';
    }

    function get_rotate()
    {
        $r = $this->db->query("CALL sp_system_email_rotate_get()")
                        ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return json_decode($r->data);
    }
}
?>

<?php

class F_misc extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "f_misc";
        $this->table_key = "F_MiscID";
    }

    function get_payable_info ()
    {
        $data = ['payable'=>0,'debt'=>0];
        
        $r = $this->db->query("SELECT fn_invoice_payable_30() as x")->row();
        $data['payable'] = $r->x;

        $r = $this->db->query("SELECT fn_purchase_debt_30() as x")->row();
        $data['debt'] = $r->x;

        return $data;
    }
}

?>
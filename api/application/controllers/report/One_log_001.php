<?php

// Report Mutasi Barang per Gudang
class One_log_001 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'LOG-001';
        $this->wQty = 2;

        $this->load->model("log/log_activity");
    }

    function search()
    {
        $r = $this->log_activity->search(['search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate']]);
        $this->sys_ok($r);
    }
}
?>

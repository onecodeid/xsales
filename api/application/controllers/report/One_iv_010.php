<?php

// Report All Stock All Warehouse

class One_iv_010 extends RPT_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'IV-010';

        $this->load->model("report/r_reportinventory");
    }

    function index() {
        die('OK');
    }

    function search()
    {
        $prm = [
            'search'=>'%'.(isset($this->sys_input['search'])?$this->sys_input['search']:'').'%', 
            'warehouse_id' => isset($this->sys_input['warehouse_id'])?$this->sys_input['warehouse_id']:0,
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate']
        ];
        $r = $this->r_reportinventory->iv_010($prm);
        $this->sys_ok($r);
    }

    function search_nominal()
    {
        $prm = [
            'search'=>'%'.(isset($this->sys_input['search'])?$this->sys_input['search']:'').'%', 
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate']
        ];
        $r = $this->r_reportinventory->iv_010_2($prm);
        $this->sys_ok($r);
    }

    // function copyRowFull(&$ws_from, &$ws_to, $row_from, $row_to) {
    //     $ws_to->getRowDimension($row_to)->setRowHeight($ws_from->getRowDimension($row_from)->getRowHeight());
    //     $lastColumn = $ws_from->getHighestColumn();
    //     ++$lastColumn;
    //     for ($c = 'A'; $c != $lastColumn; ++$c) {
    //       $cell_from = $ws_from->getCell($c.$row_from);
    //       $cell_to = $ws_to->getCell($c.$row_to);
    //       $cell_to->setXfIndex($cell_from->getXfIndex()); // black magic here
    //       $cell_to->setValue($cell_from->getValue());
    //     }
    // }
}
?>

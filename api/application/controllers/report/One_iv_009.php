<?php

// Report All Stock All Warehouse

class One_iv_009 extends RPT_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'IV-009';

        $this->load->model("report/r_reportinventory");
    }

    function index() {
        die('OK');
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'warehouse_id' => isset($this->sys_input['warehouse_id'])?$this->sys_input['warehouse_id']:0
        ];
        $r = $this->r_reportinventory->iv_009($prm);
        $this->sys_ok($r);
    }

    function excel()
    { 
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'warehouse_id'=>isset($this->sys_input['warehouse_id'])?$this->sys_input['warehouse_id']:0
        ];
        $r = $this->r_reportinventory->iv_009($prm);
        $this->load->library("Excel");

        // Get data
        $grand_total = 0;

        //WAREHOUSE
        $warehouse_name = 'SEMUA GUDANG';
        $this->load->model("master/m_warehouse");
        $warehouse = $this->m_warehouse->get($this->sys_input['warehouse_id']);
        if ($warehouse) $warehouse_name = $warehouse->warehouse_name;
        
        $filename = "laporan_barang_slow_moving_".date('d.m.Y', strtotime($this->input->get('sdate'))).".xls";

        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_iv_009.xls");

            // $data = [];
            $myLine = 5;
            $as = $objPHPExcel->getActiveSheet();
            
            // $g_total = [0, 0, 0, 0, 0, 0, 0];
            foreach ($r as $k => $v)
            {
                $mapData = ['A' => $k+1,
                                'B' => $v['warehouse_name'],
                                'C' => $v['item_code'],
                                'D' => $v['item_name'],
                                'E' => $v['stock_qty'],
                                'F' => $v['unit_name'],
                                'G' => $v['item_hpp'],
                                'H' => $v['stock_qty'] * $v['item_hpp']
                            ];
                foreach ($mapData as $m => $n) { $as->setCellValue($m.$myLine, "{$n}"); }
                $myLine++;

                $grand_total += $v['stock_qty'] * $v['item_hpp'];
            }

            // GRAND TOTAL
            $this->copyRowFull($as, $as, 4, $myLine);
            $mapData = ['H' => $grand_total
                    ];
            foreach ($mapData as $m => $n)
            {
                $as->setCellValue($m.$myLine, "{$n}");
            }

            $as->removeRow(3,2);
            $as->setCellvalue("I2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("D1", $warehouse_name);

            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Cache-Control: max-age=0");
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            ob_clean();
            $objWriter->save("php://output");

        }   
    }

    function copyRowFull(&$ws_from, &$ws_to, $row_from, $row_to) {
        $ws_to->getRowDimension($row_to)->setRowHeight($ws_from->getRowDimension($row_from)->getRowHeight());
        $lastColumn = $ws_from->getHighestColumn();
        ++$lastColumn;
        for ($c = 'A'; $c != $lastColumn; ++$c) {
          $cell_from = $ws_from->getCell($c.$row_from);
          $cell_to = $ws_to->getCell($c.$row_to);
          $cell_to->setXfIndex($cell_from->getXfIndex()); // black magic here
          $cell_to->setValue($cell_from->getValue());
        }
    }
}
?>

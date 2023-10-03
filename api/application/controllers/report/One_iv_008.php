<?php

// Report All Stock All Warehouse

class One_iv_008 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    var $gsub_total = 0;
    var $gsub_total_ppn = 0;
    var $gsub_total_qty = 0;
    var $gunit = "";
    var $gitem = "";

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'IV-008';

        $this->load->model("report/r_reportinventory");
    }

    function index() {
        // $this->pdf = new PDF("P","cm",array(21,29.7));
        // $this->pdf->SetAutoPageBreak(true,1);

        // $this->pdf->rptclass = $this;
        // $this->pdf->setRptTitle('Laporan Pergerakan Barang per Gudang');
        // $this->pdf->setRptSubtitle('Periode '.date('d M Y', strtotime($this->sys_input['sdate'])).' s/d '.date('d M Y', strtotime($this->sys_input['edate'])).' ');
        // $this->pdf->header_func = "my_header_recapt";
        // $this->pdf->footer_func = "my_footer";
       
        // $this->pdf->Output();
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'category_id'=>isset($this->sys_input['category_id'])?$this->sys_input['category_id']:0,
            'warehouse_id'=>isset($this->sys_input['warehouse_id'])?$this->sys_input['warehouse_id']:0
        ];
        $r = $this->r_reportinventory->iv_008($prm);
        $this->sys_ok($r);
    }

    function excel()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>1, 'limit'=>99999,
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'category_id'=>isset($this->sys_input['category_id'])?$this->sys_input['category_id']:0,
            'warehouse_id'=>isset($this->sys_input['warehouse_id'])?$this->sys_input['warehouse_id']:0
        ];
        $r = $this->r_reportinventory->iv_008($prm);

        $this->load->library("Excel");

        // Get data
        // $this->load->model('report/r_report');

        // $sdate = date('Y-m-d');
        // $edate = date('Y-m-d');
        // $levelid = 0;
        // if (isset($this->sys_input['sdate'])) $sdate = $this->sys_input['sdate'];
        // if (isset($this->sys_input['edate'])) $edate = $this->sys_input['edate'];

        // $r = $this->r_report->one_fin_002( $sdate, $edate );
        //
        $grand_total = 0;
        $sub_total = 0;
        
        $filename = "laporan_nilai_stok_per_gudang_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";
        
        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_iv_008.xls");

            // $objPHPExcel->getActiveSheet()->setTitle($r['records'][0]['warehouse_name']);
            // echo json_encode($r);
            foreach ($r['records'] as $k => $v)
            {
                if ($k > 0)
                {
                    $tempSheet = $objPHPExcel->getSheet(0)->copy();
                    $tempSheet->setTitle($v['warehouse_name']);

                    $objPHPExcel->addSheet($tempSheet);
                    unset($tempSheet);
                }
            }
            foreach ($r['records'] as $k => $v)
            {
                $objPHPExcel->setActiveSheetIndex($k);

                $as = $objPHPExcel->getActiveSheet();
                $as->setTitle($v['warehouse_name']);
                $as->setCellValue("E1", $v['warehouse_name']);

                $myLine = 5;
                $no = 1;
                $total = 0;
                foreach ($v['details'] as $l => $w)
                {
                    $mapData = ['A' => $no,
                                'B' => $w->category_name,
                                'C' => $w->item_code,
                                'D' => $w->item_name,
                                'E' => $w->log_a4_qty,
                                'F' => $w->unit_name,
                                'G' => $w->item_hpp,
                                'H' => $w->log_a4_qty * $w->item_hpp,
                                // 'H' => $w->stock_qty < 0 ? $w->stock_qty : 0,
                                // 'I' => $w->stock_after_qty
                            ];
                    foreach ($mapData as $m => $n)
                    {
                        $as->setCellValue($m.$myLine, "{$n}");
                    }

                    $no++;
                    $myLine++;
                    $total += $w->log_a4_qty * $w->item_hpp;
                }

                $this->copyRowFull($as, $as, 4, $myLine);
                $as->setCellValue('H'.$myLine, $total);
                $as->setCellvalue("I2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");
                $as->removeRow(3,2);
            }
            unset($tempSheet);
            
            // $as = $objPHPExcel->getActiveSheet();
            // $cur_cat = 0;
            // $g_total = [0, 0, 0, 0];
            // foreach ($r['records'] as $k => $v)
            // {
            //     // TITLE
            //     if ($k == 0)
            //     {
            //         $as->setCellValue('E1', strtoupper($v['warehouse_name']));
            //     }

            //     $this->copyRowFull($as, $as, 3, $myLine);
            //     $as->mergeCells("A{$myLine}:C{$myLine}");
            //     $as->mergeCells("D{$myLine}:I{$myLine}");
            //     $as->setCellValue("A{$myLine}", $v['category_name']);
            //     $as->setCellValue("D{$myLine}", $v['item_name']);
            //     $myLine++;

            //     foreach ($v['logs'] as $l => $w)
            //     {
            //         $no = $l + 1;
            //         $mapData = ['A' => $no,
            //                     'B' => $w->stock_date,
            //                     'C' => $w->ref_number,
            //                     'D' => $w->type_text,
            //                     'E' => $w->unit_name,
            //                     'F' => $w->stock_before_qty,
            //                     'G' => $w->stock_qty > 0 ? $w->stock_qty : 0,
            //                     'H' => $w->stock_qty < 0 ? $w->stock_qty : 0,
            //                     'I' => $w->stock_after_qty
            //                 ];
            //         foreach ($mapData as $m => $n)
            //         {
            //             $as->setCellValue($m.$myLine, "{$n}");
            //         }

            //         $myLine++;
            //     }
                
                
            //     $as->setCellValue('A'.$myLine, 'SALDO AWAL & AKHIR');
            //     $as->setCellValue('F'.$myLine, $v['logs'][0]->stock_before_qty);
            //     $as->setCellValue('G'.$myLine, $v['stock_in_qty']);
            //     $as->setCellValue('H'.$myLine, $v['stock_out_qty']);
            //     $as->setCellValue('I'.$myLine, $v['logs'][sizeof($v['logs'])-1]->stock_after_qty);
            //     $as->mergeCells("A{$myLine}:D{$myLine}");
            //     $as->getStyle('A'.$myLine.':I'.$myLine)
            //         ->applyFromArray([
            //             'borders' => array(
            //                 'bottom' => array(
            //                     'style' => PHPExcel_Style_Border::BORDER_DOUBLE
            //                 ),
            //                 'top' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN ]
            //             )
            //         ]);
            //     $as->getStyle('A'.$myLine.':I'.$myLine)->getFont()->setBold( true );
            //     $as->getStyle('A'.$myLine)
            //     ->applyFromArray([
            //         'alignment' => array(
            //             'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            //         )
            //     ]);
            //         $myLine++;
            //         $myLine++;

                

            // $as->removeRow(3,4);
            // $as->setCellvalue("J2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            // $as->setCellvalue("J3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");
            


            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Cache-Control: max-age=0");
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            ob_clean();
            $objWriter->save("php://output");
            

        }   
        $this->sys_ok(["report_url"=>$this->REPORT_EXCEL_URL.$filename]);
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

<?php

// Report Fee / Komisi Per Admin
//

class One_fin_002_excel extends RPT_Controller
{
    var $report_code;
    // var $pdf;

    function __construct()
    {
        parent::__construct();

        $this->load->library("Excel");
        $this->report_code = 'FIN-001';
    }

    function index() {
        
    }
}
?>

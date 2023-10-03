<?php

// Report All Stock All Warehouse

class One_fin_011 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'FIN-011';
        $this->wQty = 2;

        $this->load->model("report/r_reportfinance");
    }

    function excel()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'staff_id'=>isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0
        ];
        $r = $this->r_reportfinance->fin_011($prm);

        $this->load->library("Excel");

        // Get data
        $grand_total = 0;
        $sub_total = 0;
        
        $filename = "laporan_pembayaran_piutang_pelanggan_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";
        
        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_fin_011.xls");

            // $data = [];
            $myLine = 7;
            $as = $objPHPExcel->getActiveSheet();
            $g_total = [0, 0, 0];
            foreach ($r as $k => $v)
            {
                $this->copyRowFull($as, $as, 3, $myLine);
                $as->mergeCells("A{$myLine}:E{$myLine}");
                $as->setCellValue("A{$myLine}", $v['customer_name']);
                $myLine++;

                // $total = [0, 0, 0, 0, 0, 0, 0];
                foreach ($v['invoices'] as $l => $w)
                {
                    $no = $l + 1;
                    $mapData = ['A' => $no,
                                'B' => $w->invoice_date,
                                'C' => $w->invoice_number,
                                'D' => $w->sales_name,
                                'E' => $w->invoice_duedate,
                                'F' => $w->term_duration,
                                'G' => $w->invoice_grandtotal,
                                // 'H' => $w->invoice_paid,
                                'K' => $w->invoice_unpaid,
                                // 'F' => $w->invoice_unpaid,
                                // 'G' => ($w->invoice_diff_date <= 0 ? $w->invoice_unpaid : 0),
                                // 'H' => ($w->invoice_diff_date <= 30 && $w->invoice_diff_date > 0 ? $w->invoice_unpaid : 0),
                                // 'I' => ($w->invoice_diff_date <= 60 && $w->invoice_diff_date > 30  ? $w->invoice_unpaid : 0),
                                // 'J' => ($w->invoice_diff_date <= 90 && $w->invoice_diff_date > 60  ? $w->invoice_unpaid : 0),
                                // 'K' => ($w->invoice_diff_date <= 120 && $w->invoice_diff_date > 90  ? $w->invoice_unpaid : 0),
                                // 'L' => ($w->invoice_diff_date > 120 ? $w->invoice_unpaid : 0),
                            ];
                    foreach ($mapData as $m => $n)
                    {
                        $as->setCellValue($m.$myLine, "{$n}");
                    }

                    foreach ($w->payments as $m => $n)
                    {
                        if ($m > 0) $myLine++;
                        $as->setCellValue("H{$myLine}", $n->pay_date);
                        $as->setCellValue("I{$myLine}", $n->pay_number);
                        $as->setCellValue("J{$myLine}", $n->pay_total);
                    }

                    $myLine++;
                }

                $this->copyRowFull($as, $as, 4, $myLine);
                $as->mergeCells("A{$myLine}:E{$myLine}");
                $as->setCellValue("A{$myLine}", 'SUB TOTAL');
                $as->setCellValue("G{$myLine}", $v['total_grandtotal']);
                $as->setCellValue("J{$myLine}", $v['total_paid']);
                $as->setCellValue("K{$myLine}", $v['total_unpaid']);

                $range = 'A'.$myLine.':K'.$myLine;
                $BStyle = array(
                    'borders' => array(
                      'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                      ),
                      'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                      )
                    )
                  );
                $as->getStyle($range)
                    ->getFont()->setBold(true);

                // Set top and bottom borders
                $as->getStyle($range)->applyFromArray($BStyle);


                $myLine++;

                
                // $total[0] = $v['total_unpaid'];
                // $total[1] = $v['total_ongoing'];
                // $total[2] = $v['total_30'];
                // $total[3] = $v['total_60'];
                // $total[4] = $v['total_90'];
                // $total[5] = $v['total_120'];
                // $total[6] = $v['total_rest'];
                
                $g_total[0] += $v['total_grandtotal'];
                $g_total[1] += $v['total_paid'];
                $g_total[2] += $v['total_unpaid'];
                // $g_total[3] += $v['total_60'];
                // $g_total[4] += $v['total_90'];
                // $g_total[5] += $v['total_120'];
                // $g_total[6] += $v['total_rest'];

                // $this->copyRowFull($as, $as, 6, $myLine);
                // $mapData = ['F' => $total[0],
                //             'G' => $total[1],
                //             'H' => $total[2],
                //             'I' => $total[3],
                //             'J' => $total[4],
                //             'K' => $total[5],
                //             'L' => $total[6]
                //         ];
                // foreach ($mapData as $m => $n)
                // {
                //     $as->setCellValue($m.$myLine, "{$n}");
                // }
                // $as->getStyle('A'.$myLine)
                //     ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);

                // $as->mergeCells("A{$myLine}:E{$myLine}");
                // $myLine++;
                // $myLine++;
            }

            // GRAND TOTAL
            $myLine++;
            $this->copyRowFull($as, $as, 6, $myLine);
            $as->mergeCells("A{$myLine}:F{$myLine}");
            $mapData = ['G' => $g_total[0],
                            'J' => $g_total[1],
                            'K' => $g_total[2]
                ];
            //                 'I' => $g_total[3],
            //                 'J' => $g_total[4],
            //                 'K' => $g_total[5],
            //                 'L' => $g_total[6]
            //             ];
            foreach ($mapData as $m => $n)
            {
                $as->setCellValue($m.$myLine, "{$n}");
            }
            // $as->getStyle('A'.$myLine)
            //     ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);

            $as->removeRow(3,4);
            $as->setCellvalue("L2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("L3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");
            
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

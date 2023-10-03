<?php

// Report Laba Rugi
//

class One_fin_003_2 extends RPT_Controller
{
    var $report_code;
    var $balances;
    var $width;
    var $accs;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'FIN-003';
        $this->balances = [];
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        $this->pdf->setRptTitle("LAPORAN LABA RUGI");
        $this->pdf->setRptSubTitle("Per " . date("d M Y", strtotime($this->sys_input['edate'])));
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_fin_003_2( $this->input->get('sdate'), $this->input->get('edate') );
        if ($r)
        {
            $data = $r;
            $this->pdf->SetMargins(1.5, 1.5, 1.5);
            $this->pdf->AddPage('P', 'A4');
            $this->width = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin;
            
            $subTotal = 0;
            $wNom = 3.5;
            $wName = $this->width - $wNom;
            $this->pdf->Ln(1);

            foreach ($data as $k => $v)
            {
                $this->print_title(['title'=>$v['sub_title']], 'BT', 1);

                foreach ($v['details'] as $l => $w)
                {
                    $x = (array) $w;
                    $this->print_row($x);
                }
                $this->print_title(['title'=>'TOTAL '.$v['sub_title'],'amount'=>$v['sub_total']], 'BT', 1);
                $this->pdf->Ln(0.2);

                // balances
                $this->balances[$v['sub_type']] = $v['sub_total'];

                if (strpos($v['sub_type'], 'HPP') !== false)
                {
                    $subTotal += $this->balances['INCOME.SALES'] - $this->balances['INCOME.HPP'];
                    $this->print_title(['title'=>'LABA KOTOR','amount'=>$subTotal]);
                    $this->pdf->Ln(0.2);
                }
                
                if ($v['sub_type'] == 'INCOME.EXPENSE')
                {
                    $subTotal -= $this->balances['INCOME.EXPENSE'];
                    $this->print_title(['title'=>'LABA OPERASIONAL','amount'=>$subTotal]);
                    $this->pdf->Ln(0.2);
                }

                if ($v['sub_type'] == 'INCOME.EXPENSE.OTHER')
                {
                    $subTotal += $this->balances['INCOME.OTHER'] - $this->balances['INCOME.EXPENSE.OTHER'];
                    $this->print_title(['title'=>'TOTAL PENDAPATAN DAN BEBAN LAIN','amount'=>$this->balances['INCOME.OTHER'] - $this->balances['INCOME.EXPENSE.OTHER']]);
                    $this->pdf->Ln(0.2);
                    $this->print_title(['title'=>'TOTAL LABA BERSIH','amount'=>$subTotal]);
                    $this->pdf->Ln(0.2);
                }
            }
        }


        $this->pdf->Output();
    }

//   function myFooter($me) {
//     $me->pdf->SetFont("ArialNarrow","",8);
//     $me->pdf->SetXY(1,-1);
//     $me->pdf->MultiCell(19,1,"LKK/MCU/2015/" ,"","C");
//     $me->pdf->SetXY(1,-1);
//     $me->pdf->MultiCell(19,1,"Halaman : " . $me->pdf->PageNo() ,"","R");
//   }

//   function print_total($me, $total)
//   {
//     $me->pdf->Ln(0.25);
//     $me->pdf->Cell(0.5, 0.7, '', '', 0);
//     $me->pdf->Cell(12.5, 0.7, 'TOTAL', 'LBT', 0, 'C', 1);
//     $me->pdf->Cell(6, 0.7, number_format($total, 0), 'RBTL', 0, 'R', 1);
//     $me->pdf->Ln(0.7);
//   }

//   function print_grandtotal($me, $gtotal)
//   {
//     $me->pdf->Ln(0.25);
//     $me->pdf->Cell(0.5, 0.7, '', '', 0);
//     $me->pdf->Cell(12.5, 0.7, 'GRAND TOTAL', 'LBT', 0, 'C', 1);
//     $me->pdf->Cell(6, 0.7, number_format($gtotal, 0), 'RBTL', 0, 'R', 1);
//     $me->pdf->Ln(0.7);
//   }

    function print_row($d)
    {
        $wNom = 3.5;
        $wName = $this->width - $wNom;
        $this->pdf->SetStyle('B', false);
        $this->pdf->Cell($wName - 1.5, 0.7, $d['account_name'], 'L', 0, 'L', 0);
        $this->pdf->Cell(0.5, 0.7, 'Rp', '', 0, 'R', 0);
        $this->pdf->Cell($wNom, 0.7, $this->absx($d['journal_balance']), 'R', 0, 'R', 0);
        $this->pdf->Ln(0.7);
    }

    function print_title($d, $border = 'BT', $marginLeft = 0)
    {
        $wNom = 3.5;
        $wName = $this->width - $wNom;
        $h = 0.7;

        $this->pdf->SetFillColor(173, 216, 230);
        $this->pdf->SetStyle('B', true);
        // if ($marginLeft>0)
        //     $this->pdf->Cell($marginLeft, $h, '', '', 0, 'L', 1);

        if (!isset($d['amount']))
            $this->pdf->Cell($wName - $marginLeft + $wNom, $h, strtoupper($d['title']), 'LR'.$border, 0, 'L', 1);
        else
        {
            $this->pdf->Cell($wName - $marginLeft, $h, strtoupper($d['title']), $border.'L', 0, 'L', 1);
            $this->pdf->Cell($wNom, $h, $this->absx($d['amount']), $border.'R', 0, 'R', 1);
        }
            
        $this->pdf->Ln($h);
    }

    function absx($x)
    {
        return ($x < 0 ? "- ".number_format(abs($x))."" : number_format($x));
    }

    function endLine($me, $w = 0)
    {
        $w == 0 ? $this->width : 0;
        $me->Cell($w, 0.2, '' , 'T', 0, 'C', 0);
        $me->Ln(0.2);
    }

    function search()
    {
        $this->load->model('report/r_report');
        $r = $this->r_report->one_fin_003_2( $this->sys_input['sdate'], $this->sys_input['edate'] );
        
        $this->sys_ok($r);
    }

    function excel()
    { 
        $this->load->model('report/r_report');
        $r = $this->r_report->one_fin_003_2( $this->sys_input['sdate'], $this->sys_input['edate'] );

        $this->load->library("Excel");


        
        $filename = "laporan_laba_rugi_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";

        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_fin_003.xls");

            // $data = [];
            $myLine = 5;
            $as = $objPHPExcel->getActiveSheet();

            $grossProfit = 0;
            $operationalProfit = 0;
            $nettProfit = 0;
            $others = 0;

            foreach ($r as $k => $v)
            {
                $this->copyRowFull($as, $as, 3, $myLine);
                $as->mergeCells("A{$myLine}:B{$myLine}");

                $as->setCellValue("A{$myLine}", $v['sub_title']);
                $as->getStyle('A'.$myLine.':C'.$myLine)
                        ->applyFromArray(['font' => ['bold' => true]]);
                foreach ($v['details'] as $l => $w)
                {
                    $myLine++;
                    $mapData = ['A' => $w->account_code,
                                'B' => $w->account_name,
                                'C' => $w->journal_balance,
                            ];
                    foreach ($mapData as $m => $n)
                    {
                        $as->setCellValue($m.$myLine, "{$n}");
                    }
                }

                $myLine++;
                $this->copyRowFull($as, $as, 3, $myLine);
                $as->mergeCells("A{$myLine}:B{$myLine}");
                
                $as->setCellValue("A{$myLine}", 'TOTAL '.$v['sub_title']);
                $as->setCellValue("C{$myLine}", $v['sub_total']);

                $as->getStyle('A'.$myLine.':C'.$myLine)
                        ->applyFromArray(
                            [
                                'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('rgb' => 'FFFFFF'), 'endcolor' => array('rgb' => 'FFFFFF')],
                                'font' => ['bold' => true],
                                'borders' => ['top' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ]],
                            ]
                );
                $myLine++;

                if ($v['sub_type'] == 'INCOME.SALES') $grossProfit += $v['sub_total'];
                if ($v['sub_type'] == 'INCOME.HPP') $grossProfit -= $v['sub_total'];
                if ($v['sub_type'] == 'INCOME.EXPENSE') $operationalProfit += $grossProfit - $v['sub_total'];
                if ($v['sub_type'] == 'INCOME.OTHER') $others += $v['sub_total'];
                if ($v['sub_type'] == 'INCOME.EXPENSE.OTHER') $others -= $v['sub_total'];
                
                if(array_search($v['sub_type'], ['INCOME.HPP', 'INCOME.EXPENSE', 'INCOME.EXPENSE.OTHER']) !== false)
                {
                    $myLine++;
                    $this->copyRowFull($as, $as, 4, $myLine);
                    $as->mergeCells("A{$myLine}:B{$myLine}");
                    $as->getStyle('A'.$myLine.':C'.$myLine)
                        ->applyFromArray(
                            [
                                'font' => ['bold' => true],
                                'borders' => ['top' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ]],
                            ]
                        );
                }
                    
                if ($v['sub_type'] == 'INCOME.HPP')
                {
                    $as->setCellValue("A{$myLine}", 'LABA KOTOR');
                    $as->setCellValue("C{$myLine}", $grossProfit);
                    $myLine++;
                }

                if ($v['sub_type'] == 'INCOME.EXPENSE')
                {
                    $as->setCellValue("A{$myLine}", 'LABA OPERASIONAL');
                    $as->setCellValue("C{$myLine}", $operationalProfit);
                    $myLine++;
                }

                if ($v['sub_type'] == 'INCOME.EXPENSE.OTHER')
                {
                    $as->setCellValue("A{$myLine}", 'TOTAL PENDAPATAN DAN BEBAN LAIN');
                    $as->setCellValue("C{$myLine}", $others);
                    $myLine++;

                    $nettProfit = $operationalProfit += $others;
                    $this->copyRowFull($as, $as, 4, $myLine);
                    $as->setCellValue("A{$myLine}", 'LABA BERSIH');
                    $as->setCellValue("C{$myLine}", $nettProfit);
                    
                    $as->getStyle('A'.$myLine.':C'.$myLine)
                        ->applyFromArray(
                            [
                                'font' => ['bold' => true],
                                'borders' => ['top' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ],
                                            'bottom' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ]],
                            ]
                        );
                    $myLine++;
                }
                

                $myLine++;
            }

            // GRAND TOTAL
            // $this->copyRowFull($as, $as, 6, $myLine);
            // $as->mergeCells("A{$myLine}:C{$myLine}");
            // $mapData = ['D' => $g_total[0],
            //                 'E' => $g_total[1],
            //                 'F' => $g_total[2],
            //                 'G' => $g_total[3],
            //                 'H' => $g_total[4]
            //             ];
            // foreach ($mapData as $m => $n)
            // {
            //     $as->setCellValue($m.$myLine, "{$n}");
            // }
            $as->getStyle('A'.$myLine)
                ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);

            $as->removeRow(3,2);
            $as->setCellvalue("D2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("D3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");

            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Cache-Control: max-age=0");
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            ob_clean();
            $objWriter->save("php://output");

        }   
        // $this->sys_ok(["report_url"=>$this->REPORT_EXCEL_URL.$filename]);
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

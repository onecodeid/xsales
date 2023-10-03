<?php

// Report Laba Rugi
//

class One_fin_008_2 extends RPT_Controller
{
    var $report_code;
    var $balances;
    var $width;
    var $accs;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'FIN-008';
        $this->balances = [];
    }

    function search()
    {
        $this->load->model('report/r_reportfinance');
        $r = $this->r_reportfinance->one_fin_008( $this->sys_input['sdate'], $this->sys_input['edate'] );
        
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

    function search_detail()
    {
        $this->load->model('report/r_reportfinance');
        $d = $this->sys_input;
        $d['search'] = isset($d['search']) ? "%{$d['search']}%" : "%";
        $r = $this->r_reportfinance->one_fin_008_detail( $d );
        
        $this->sys_ok($r);
    }
}
?>

<?php

// Report Laba Rugi
//

class One_fin_010 extends RPT_Controller
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
        $this->balances = ['sales'=>0, 'hpp'=>0, 'expense'=>0, 'other'=>0, 'expense_other'=>0];
        $this->accs = [
            'INCOME.SALES' => ['balance'=>0,'title'=>'PENJUALAN BERSIH'],
            'INCOME.HPP' => ['balance'=>0],
            'INCOME.EXPENSE' => ['balance'=>0,'title'=>'TOTAL BIAYA OPERASIONAL']
        ];
    }

    function index() {
        $this->load->model('report/r_reportfinance');
        $r = $this->r_reportfinance->one_fin_010( $this->sys_input['sdate'], $this->sys_input['edate'] );

        $this->sys_ok($r);
    }

    function excel()
    { 
        $this->load->model('report/r_reportfinance');
        $data = $this->r_reportfinance->one_fin_010( $this->sys_input['sdate'], $this->sys_input['edate'] );

        $this->load->library("Excel");
        $filename = "laporan_analisa_laba_rugi_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";

        if ($data)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_fin_010.xls");

            // $data = [];
            $myBaseLine = 5;
            $myLine = 5;
            $as = $objPHPExcel->getActiveSheet();

            $aCols = [];
            $pCols = [];
            $salesLine = 1;
            $tCols = [];

            foreach ($data as $kk => $vv)
            {
                $myLine = $myBaseLine;
                $r = $vv['data'];

                $aCol = chr(ord('C') + (2*$kk));
                $pCol = chr(ord($aCol) + 1);
                $aCols[] = $aCol;
                $pCols[] = $pCol;
                $as->setCellValue("{$aCol}2", $vv['name']);

                $grossProfit = 0;
                $operationalProfit = 0;
                $nettProfit = 0;
                $others = 0;
                $sales = 0;

                foreach ($r as $k => $v)
                {
                    if ($kk == 0)
                    {
                        $this->copyRowFull($as, $as, 3, $myLine);
                        $as->mergeCells("A{$myLine}:B{$myLine}");
                        $as->setCellValue("A{$myLine}", $v['sub_title']);
                        $as->getStyle('A'.$myLine.':C'.$myLine)
                                ->applyFromArray(['font' => ['bold' => true]]);
                    }
                    
                    foreach ($v['details'] as $l => $w)
                    {    
                        $w = json_decode(json_encode($w));
                        $myLine++;
                        if ($kk == 0)
                        {
                            $mapData = ['A' => $w->account_code,
                                    'B' => $w->account_name,
                                    $aCol => $w->journal_balance,
                                ];
                        }
                        else
                        {
                            $mapData = [ $aCol => $w->journal_balance ];
                        }
                        foreach ($mapData as $m => $n)
                        {
                            $as->setCellValue($m.$myLine, "{$n}");
                        }
                    }

                    $myLine++;

                    if ($kk == 0)
                    {
                        $this->copyRowFull($as, $as, 3, $myLine);
                        $as->mergeCells("A{$myLine}:B{$myLine}");
                        
                        $as->setCellValue("A{$myLine}", 'TOTAL '.$v['sub_title']);
                    }
                    
                    $as->setCellValue("{$aCol}{$myLine}", $v['sub_total']);
                    if ($v['sub_type'] == 'INCOME.SALES') {
                        $sales = $v['sub_total'];
                        $salesCol = $aCol.$myLine;

                        $salesLine = $myLine;
                        $tCols[] = $myLine;

                        $as->setCellValue("{$pCol}{$myLine}", "={$aCol}{$myLine}*100/{$salesCol}");
                    }
                    // $as->setCellValue("{$pCol}{$myLine}", $v['sub_total'] * 100 / $sales);

                    $as->getStyle('A'.$myLine.':'.$pCol.$myLine)
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
                        if ($kk == 0) {
                            $this->copyRowFull($as, $as, 4, $myLine);
                            $as->mergeCells("A{$myLine}:B{$myLine}"); 
                        }
                        
                        $as->getStyle('A'.$myLine.':'.$pCol.$myLine)
                            ->applyFromArray(
                                [
                                    'font' => ['bold' => true],
                                    'borders' => ['top' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ]],
                                ]
                            );
                        $tCols[] = $myLine;
                    }
                        
                    if ($v['sub_type'] == 'INCOME.HPP')
                    {
                        $as->setCellValue("A{$myLine}", 'LABA KOTOR');
                        $as->setCellValue("{$aCol}{$myLine}", $grossProfit);
                        $as->setCellValue("{$pCol}{$myLine}", "={$aCol}{$myLine}*100/{$salesCol}");
                        $myLine++;
                    }

                    if ($v['sub_type'] == 'INCOME.EXPENSE')
                    {
                        $as->setCellValue("A{$myLine}", 'LABA OPERASIONAL');
                        $as->setCellValue("{$aCol}{$myLine}", $operationalProfit);
                        $as->setCellValue("{$pCol}{$myLine}", "={$aCol}{$myLine}*100/{$salesCol}");
                        $myLine++;
                    }

                    if ($v['sub_type'] == 'INCOME.EXPENSE.OTHER')
                    {
                        $as->setCellValue("A{$myLine}", 'TOTAL PENDAPATAN DAN BEBAN LAIN');
                        $as->setCellValue("{$aCol}{$myLine}", $others);
                        $myLine++;

                        $nettProfit = $operationalProfit += $others;
                        if ($kk == 0)
                            $this->copyRowFull($as, $as, 4, $myLine);
                        $as->setCellValue("A{$myLine}", 'LABA BERSIH');
                        $as->setCellValue("{$aCol}{$myLine}", $nettProfit);
                        $as->setCellValue("{$pCol}{$myLine}", "={$aCol}{$myLine}*100/{$salesCol}");
                        
                        $as->getStyle('A'.$myLine.':'.$pCol.$myLine)
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
                $eLine = $myLine - 1;


                // PERCENTAGE
                $ppCol = $aCol;
                for($myLine = $myBaseLine; $myLine <= $eLine; $myLine++)
                {
                    $ppVal = $as->getCell("{$ppCol}{$myLine}")->getValue();
                    $pVal = $as->getCell("{$pCol}{$myLine}")->getValue();

                    if ($ppVal != "" && $pVal == "")
                        $as->setCellValue("{$pCol}{$myLine}", "={$ppCol}{$myLine}*100/{$salesCol}");
                }
            }

            // GRAND TOTAL
            $kk = sizeof($data);
            $aCol = chr(ord('C') + (2*$kk));
            $pCol = chr(ord($aCol) + 1);
            $aaCol = chr(ord('C') + (2*($kk-1)));

            $as->setCellValue("{$aCol}2", 'GRAND TOTAL');
            $ppCol = $aCol;
            for($myLine = $myBaseLine; $myLine <= $eLine; $myLine++)
            {
                $aaVal = $as->getCell("{$aaCol}{$myLine}")->getValue();
                $formula = '=';
                foreach($aCols as $k => $v) $formula .= $v.$myLine . '+';
                if ($aaVal != "") {
                    $as->setCellValue("{$aCol}{$myLine}", "{$formula}0");
                    $as->setCellValue("{$pCol}{$myLine}", "={$aCol}{$myLine}*100/{$aCol}{$salesLine}");
                }
                    
            }

            foreach ($tCols as $k => $v)
            {
                $as->getStyle($aCol.$v.':'.$pCol.$v)
                            ->applyFromArray(
                                [
                                    'font' => ['bold' => true],
                                    'borders' => ['top' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ]],
                                ]
                            );
            }
            // END OF GRAND TOTAL

            // SCRIPT PENGHILANG JEJAK
            $xCol = chr(ord($pCol)+1);
            $as->getStyle($xCol.'2:Z193')
                            ->applyFromArray(
                                [
                                    'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('rgb' => 'FFFFFF'), 'endcolor' => array('rgb' => 'FFFFFF')],
                                ]
                    );
            
            $as->getStyle('A'.$myLine)
                ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);

            $as->removeRow(3,2);
            $as->setCellvalue("I1", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("K1", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");

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

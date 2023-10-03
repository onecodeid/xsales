<?php

// Report Fee / Komisi Per Admin
//

class One_fin_001 extends RPT_Controller
{
    var $report_code;
    var $widths;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'FIN-001';
        $this->widths = ['name'=>0,'balance'=>3];

        $this->load->model("report/r_reportfinance");
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        $this->pdf->setRptTitle("NERACA SALDO");
        $this->pdf->setRptSubTitle("Per " . date("d M Y", strtotime($this->sys_input['edate'])));
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_fin_001( $this->input->get('sdate'), $this->input->get('edate') );
        // print_r($r);
        //
        $pre_credit = 0;
        $pre_debit = 0;
        $trans_debit = 0;
        $trans_credit = 0;
        $total_debit = 0;
        $total_credit = 0;
        $group_code = '';

        if ($r)
        {
            // print_r($r);
            $data = $r;
            // $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');

            $this->widths['name'] = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin - (6*$this->widths['balance']);
            // $hy = $this->pdf->GetY();

            // $this->my_header($this, 
            //     'Laporan Komisi Per Admin', 
            //     'Admin : '.$r['S_UserUsername'].' | Periode : ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' - ' .  date('d/m/Y', strtotime($this->input->get('edate'))));

            
            $this->tableHeader($this->pdf);

            // $this->pdf->SetFillColor(255,255,255);
            // $this->pdf->SetTextColor(0,0,0);
            // $this->pdf->SetFont('Arial','', 9);
            foreach ($data as $k => $v)
            {
                if ($this->pdf->GetY() > 19) {
                    $this->endLine($this->pdf);
                    $this->pdf->AddPage('L', 'A4');
                    $this->tableHeader($this->pdf);
                }

                if ($group_code != $v['group_code']) {
                    if ($this->pdf->GetY() > 18) {
                        $this->endLine($this->pdf);
                        $this->pdf->AddPage('L', 'A4');
                        $this->tableHeader($this->pdf);
                    }
                    $this->pdf->Cell($this->widths['name'] + (6*$this->widths['balance']), 0.7, $v['group_name'], 'LRTB', 0, 'L', 0);
                    $this->pdf->Ln(0.7);
                    $group_code = $v['group_code'];
                }
                    
            //     $cl_name = '';
            //     if ($v['M_CustomerLevelCode'] == 'CUST.DISTRIBUTOR') $cl_name = 'Dist';
            //     if ($v['M_CustomerLevelCode'] == 'CUST.AGENCY') $cl_name = 'Agen';
            //     if ($v['M_CustomerLevelCode'] == 'CUST.RESELLER') $cl_name = 'Resl';
            //     if ($v['M_CustomerLevelCode'] == 'CUST.ENDUSER') $cl_name = 'User';
                $prefix_len = ($v['level']+1)/2;
                $b_debit =  $v['b_debit'] >= $v['b_credit'] ? $v['b_debit']-$v['b_credit'] : 0;
                $b_credit = $v['b_credit'] >= $v['b_debit'] ? $v['b_credit']-$v['b_debit'] : 0;

                $this->pdf->Cell($prefix_len, 0.7, '', 'L', 0, 'L', 0);
                $this->pdf->Cell($this->widths['name'] - $prefix_len, 0.7, $v['account_name'], '', 0, 'L', 0);
                $this->pdf->Cell(3, 0.7, number_format($b_debit), 'L', 0, 'R', 0);
                $this->pdf->Cell(3, 0.7, number_format($b_credit), 'L', 0, 'R', 0);
                $this->pdf->Cell(3, 0.7, number_format($v['j_debit']), 'L', 0, 'R', 0);
                $this->pdf->Cell(3, 0.7, number_format($v['j_credit']), 'L', 0, 'R', 0);

                $s_debit = $v['b_debit'] + $v['j_debit'];
                $s_credit = $v['b_credit'] + $v['j_credit'];
                $sx_debit = $s_debit >= $s_credit ? $s_debit - $s_credit : 0;
                $sx_credit = $s_debit < $s_credit ? $s_credit - $s_debit : 0;
                $this->pdf->Cell(3, 0.7, number_format($sx_debit), 'L', 0, 'R', 0);
                $this->pdf->Cell(3, 0.7, number_format($sx_credit), 'LR', 0, 'R', 0);

                $pre_credit += $b_credit;
                $pre_debit += $b_debit;
                $trans_credit += $v['j_credit'];
                $trans_debit += $v['j_debit'];
                $total_credit += $sx_credit;
                $total_debit += $sx_debit;
            //     $this->pdf->Cell(5, 0.7, $v['M_CustomerName'] . ' / ' . $cl_name, 'LB', 0, 'L', 1);
            //     $this->pdf->Cell(5, 0.7, $v['M_ItemName'] == null ? $v['M_PacketName'] : $v['M_ItemName'], 'LB', 0, 'L', 1);
            //     $this->pdf->Cell(2, 0.7, number_format($v['F_FeeAmount']), 'LB', 0, 'R', 1);
            //     $this->pdf->Cell(1, 0.7, number_format($v['F_FeeQty']), 'LB', 0, 'R', 1);
            //     $this->pdf->Cell(3, 0.7, number_format($v['F_FeeTotal']), 'LBR', 0, 'R', 1);
                $this->pdf->Ln(0.7);

            //     $grand_total += $v['F_FeeTotal'];
            }

            $this->endLine($this->pdf);

            // $this->pdf->SetFillColor(222,222,222);
            // $this->pdf->SetTextColor(0,0,0);
            // $this->pdf->Cell(16, 0.7, 'GRAND TOTAL', 'LB', 0, 'C', 1);
            // $this->pdf->Cell(3, 0.7, number_format($grand_total), 'LBR', 0, 'R', 1);

            $this->pdf->Cell($this->widths['name'], 0.7, 'TOTAL', 'LTB', 0, 'L', 1);
            $this->pdf->Cell(3, 0.7, number_format($pre_debit), 'LTB', 0, 'R', 1);
            $this->pdf->Cell(3, 0.7, number_format($pre_credit), 'LTB', 0, 'R', 1);
            $this->pdf->Cell(3, 0.7, number_format($trans_debit), 'LTB', 0, 'R', 1);
            $this->pdf->Cell(3, 0.7, number_format($trans_credit), 'LTB', 0, 'R', 1);

            $this->pdf->Cell(3, 0.7, number_format($total_debit), 'LTB', 0, 'R', 1);
            $this->pdf->Cell(3, 0.7, number_format($total_credit), 'LTBR', 0, 'R', 1);
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

    function tableHeader($me)
    {
        $me->SetFillColor(220,220,220);
        // $me->SetTextColor(255,255,255);
        $this->pdf->SetFont('Arial','', 10);
        $me->Cell($this->widths['name'], 1.4, 'NAMA REKENING' , 'LTBR', 0, 'C', 1);
        $me->Cell(6, 0.7, 'SALDO AWAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(6, 0.7, 'PERGERAKAN' , 'LTBR', 0, 'C', 1);
        $me->Cell(6, 0.7, 'SALDO AKHIR' , 'LTBR', 0, 'C', 1);
        $me->Ln(0.7);
        $me->Cell($this->widths['name'], 0.7, '' , '', 0, 'C', 0);
        $me->Cell(3, 0.7, 'DEBIT' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 0.7, 'KREDIT' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 0.7, 'DEBIT' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 0.7, 'KREDIT' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 0.7, 'DEBIT' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 0.7, 'KREDIT' , 'LTBR', 0, 'C', 1);
        $me->Ln(0.7);

        $me->SetTextColor(0, 0, 0);
    }

    function endLine($me, $w = 0)
    {
        $w == 0 ? $this->widths['name'] + $this->widths['balance'] : $w;
        $me->Cell($w, 0.2, '' , 'T', 0, 'C', 0);
        $me->Ln(0.2);
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'account_id'=>isset($this->sys_input['account_id'])?$this->sys_input['account_id']:0
        ];
        $r = $this->r_reportfinance->fin_001($prm);
        $this->sys_ok($r);
    }

    function excel()
    { 
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'account_id'=>isset($this->sys_input['account_id'])?$this->sys_input['account_id']:0
        ];
        $r = $this->r_reportfinance->fin_001($prm);

        $this->load->library("Excel");

        $grand_total = ['balance_start'=>['debit'=>0, 'credit'=>0], 'trans'=>['debit'=>0, 'credit'=>0], 'balance_end'=>['debit'=>0, 'credit'=>0]];
        $sub_total = 0;
        
        $filename = "laporan_neraca_saldo_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";

        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_fin_001.xls");

            $myLine = 7;
            $as = $objPHPExcel->getActiveSheet();
            foreach ($r as $k => $v)
            {
                $this->copyRowFull($as, $as, 4, $myLine);
                $as->mergeCells("A{$myLine}:G{$myLine}");

                $as->setCellValue("A{$myLine}", $v['group_name']);
                // $as->getStyle('C'.$myLine)
                //     ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);
                $myLine++;

                foreach ($v['details'] as $l => $w)
                {
                    $no = $l + 1;
                    $mapData = ['A' => $w->account_name,
                                'B' => $w->b_debit,
                                'C' => $w->b_credit,
                                'D' => $w->j_debit,
                                'E' => $w->j_credit,
                                'F' => (($w->b_debit + $w->j_debit) >= ($w->j_credit + $w->b_credit) ? ($w->b_debit + $w->j_debit - ($w->j_credit + $w->b_credit)) : 0),
                                'G' => (($w->j_credit + $w->b_credit) > ($w->b_debit + $w->j_debit) ? ($w->j_credit + $w->b_credit - ($w->b_debit + $w->j_debit)) : 0)
                            ];
                    foreach ($mapData as $m => $n)
                    {
                        $as->setCellValue($m.$myLine, "{$n}");
                    }
                    $grand_total['balance_start']['debit'] += $w->b_debit;
                    $grand_total['balance_start']['credit'] += $w->b_credit;
                    $grand_total['trans']['debit'] += $w->j_debit;
                    $grand_total['trans']['credit'] += $w->j_credit;
                    $grand_total['balance_end']['debit'] += $w->b_debit + $w->j_debit;
                    $grand_total['balance_end']['credit'] += $w->b_credit + $w->j_credit;

                    $myLine++;
                }
                
                // $this->copyRowFull($as, $as, 5, $myLine);
                // $mapData = ['H' => $v["balance_close"],
                //             'F' => $v["total_debit"],
                //             'G' => $v["total_credit"],
                //         ];
                // foreach ($mapData as $m => $n)
                // {
                //     $as->setCellValue($m.$myLine, "{$n}");
                // }
                // $as->setCellValue("C{$myLine}", "JUMLAH");

                // $myLine++;
                // $myLine++;
            }

            // GRAND TOTAL
            $this->copyRowFull($as, $as, 6, $myLine);
            $mapData = ['B' => $grand_total['balance_start']['debit'],
                        'C' => $grand_total['balance_start']['credit'],
                        'D' => $grand_total['trans']['debit'],
                        'E' => $grand_total['trans']['credit'],
                        'F' => $grand_total['balance_end']['debit'],
                        'G' => $grand_total['balance_end']['credit'],
                    ];
            foreach ($mapData as $m => $n)
            {
                $as->setCellValue($m.$myLine, "{$n}");
            }
            // $as->setCellValue("A{$myLine}", "GRAND TOTAL");

            $as->removeRow(4,3);
            $as->setCellvalue("H2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("H3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");

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

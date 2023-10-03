<?php

// Report Fee / Komisi Per Admin
//

class One_fin_002 extends RPT_Controller
{
    var $report_code;
    var $widths;
    var $width;

    function __construct()
    {
        parent::__construct();

        // $this->load->library("pdf");
        $this->report_code = 'FIN-002';
        $this->widths = ['date'=>3,'desc'=>0,'amount'=>3,'balance'=>4];

        $this->load->model("report/r_reportfinance");
        $this->load->model("report/r_reportsales");
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_fin_002( $this->input->get('accountid'), $this->input->get('sdate'), $this->input->get('edate') );

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        $this->pdf->setRptTitle("BUKU BESAR");
        $this->pdf->setRptSubTitle("Periode " . date("d M Y", strtotime($this->sys_input['sdate'])) . " - " . date("d M Y", strtotime($this->sys_input['edate'])));
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // print_r($r);
        //
        $grand_total = 0;
        $sub_total = 0;
        $group_code = '';
        $account_code = '';
        $balances = [];
        $header = false;
        

        foreach ($r[0] as $k => $v) {
            $balances[$v['account_code']] = $v;
        }

        if (isset($r[1]))
        {
            $data = $r[1];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');

            $this->width = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin;
            $this->widths['desc'] =  $this->width - $this->widths['date'] - ($this->widths['amount'] * 2) - $this->widths['balance'];
            // // $this->pdf->SetFillColor(255,255,255);
            // // $this->pdf->SetTextColor(0,0,0);
            // // $this->pdf->SetFont('Arial','', 9);
            
            foreach ($data as $k => $v)
            {
                $header = false;
                if ($this->pdf->GetY() > 19) {
                    $this->endLine($this->pdf);
                    $this->pdf->AddPage('L', 'A4');
                    $this->tableHeader($this->pdf, $v);
                    $header = true;
                }

                if ($account_code != $v['account_code']) {
                    $account_code = $v['account_code'];

                    $balance = 0;
                    if (isset($balances[$account_code])) {
                        $x = $balances[$account_code];
                        if ($x['account_type'] == 'A') $balance = $x['balance_debit'] - $x['balance_credit'] + $x['trans_debit'] - $x['balance_credit'];
                        else $balance = $x['balance_credit'] - $x['balance_debit'] + $x['trans_credit'] - $x['balance_debit'];
                    }

                    if (!$header) {
                        if ($k > 0)
                            $this->endLine($this->pdf);
                        if ($this->pdf->GetY() > 18)
                            $this->pdf->AddPage('L', 'A4');
                        $this->tableHeader($this->pdf, $v);
                    }
                        

                    $this->pdf->Cell($this->widths['date'], 0.7, "", 'L', 0, 'L', 0);
                    $this->pdf->Cell($this->widths['desc'], 0.7, "SALDO AWAL", 'L', 0, 'L', 0);
                    $this->pdf->Cell($this->widths['amount'], 0.7, "", 'L', 0, 'R', 0);
                    $this->pdf->Cell($this->widths['amount'], 0.7, "", 'L', 0, 'R', 0);
                    $this->pdf->Cell($this->widths['balance'], 0.7, number_format($balance), 'LR', 0, 'R', 0);
                    $this->pdf->Ln(0.7);
                }

                if ($this->pdf->GetY() > 19) {
                    $this->endLine($this->pdf);
                    $this->pdf->AddPage('L', 'A4');
                    $this->tableHeader($this->pdf, $v);
                }

                if ($r[0][0]['account_type'] == 'A') $balance = $balance + $v['journal_debit'] - $v['journal_credit'];
                else $balance = $balance + $v['journal_credit'] - $v['journal_debit'];
            //     if ($group_code != $v['group_code']) {
            //         $this->pdf->Cell(10, 0.7, $v['group_name'], 'L', 0, 'L', 0);
            //         $this->pdf->Ln(0.7);
            //         $group_code = $v['group_code'];
            //     }
                    
            // //     $cl_name = '';
            // //     if ($v['M_CustomerLevelCode'] == 'CUST.DISTRIBUTOR') $cl_name = 'Dist';
            // //     if ($v['M_CustomerLevelCode'] == 'CUST.AGENCY') $cl_name = 'Agen';
            // //     if ($v['M_CustomerLevelCode'] == 'CUST.RESELLER') $cl_name = 'Resl';
            // //     if ($v['M_CustomerLevelCode'] == 'CUST.ENDUSER') $cl_name = 'User';
            //     $prefix_len = ($v['level']+1)/2;
            //     $b_debit =  $v['b_debit'] >= $v['b_credit'] ? $v['b_debit']-$v['b_credit'] : 0;
            //     $b_credit = $v['b_credit'] >= $v['b_debit'] ? $v['b_credit']-$v['b_debit'] : 0;

            //     $this->pdf->Cell($prefix_len, 0.7, '', 'L', 0, 'L', 0);
                $this->pdf->Cell($this->widths['date'], 0.7, date("d / m / Y", strtotime($v['journal_date'])), 'L', 0, 'C', 0);
                $this->pdf->Cell($this->widths['desc'], 0.7, $v['ledger_note'], 'L', 0, 'L', 0);
                $this->pdf->Cell($this->widths['amount'], 0.7, number_format($v['journal_debit']), 'L', 0, 'R', 0);
                $this->pdf->Cell($this->widths['amount'], 0.7, number_format($v['journal_credit']), 'L', 0, 'R', 0);
                $this->pdf->Cell($this->widths['balance'], 0.7, number_format($balance), 'LR', 0, 'R', 0);
            //     $this->pdf->Cell(3, 0.7, number_format($b_credit), 'L', 0, 'R', 0);
            //     $this->pdf->Cell(3, 0.7, number_format($v['j_debit']), 'L', 0, 'R', 0);
            //     $this->pdf->Cell(3, 0.7, number_format($v['j_credit']), 'L', 0, 'R', 0);

            //     $s_debit = $v['b_debit'] + $v['j_debit'];
            //     $s_credit = $v['b_credit'] + $v['j_credit'];
            //     $sx_debit = $s_debit >= $s_credit ? $s_debit - $s_credit : 0;
            //     $sx_credit = $s_debit < $s_credit ? $s_credit - $s_debit : 0;
            //     $this->pdf->Cell(3, 0.7, number_format($sx_debit), 'L', 0, 'R', 0);
            //     $this->pdf->Cell(3, 0.7, number_format($sx_credit), 'LR', 0, 'R', 0);
            // //     $this->pdf->Cell(5, 0.7, $v['M_CustomerName'] . ' / ' . $cl_name, 'LB', 0, 'L', 1);
            // //     $this->pdf->Cell(5, 0.7, $v['M_ItemName'] == null ? $v['M_PacketName'] : $v['M_ItemName'], 'LB', 0, 'L', 1);
            // //     $this->pdf->Cell(2, 0.7, number_format($v['F_FeeAmount']), 'LB', 0, 'R', 1);
            // //     $this->pdf->Cell(1, 0.7, number_format($v['F_FeeQty']), 'LB', 0, 'R', 1);
            // //     $this->pdf->Cell(3, 0.7, number_format($v['F_FeeTotal']), 'LBR', 0, 'R', 1);
                $this->pdf->Ln(0.7);


            }

            // $this->pdf->SetFillColor(222,222,222);
            // $this->pdf->SetTextColor(0,0,0);
            // $this->pdf->Cell(16, 0.7, 'GRAND TOTAL', 'LB', 0, 'C', 1);


            $this->endLine($this->pdf);
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

    function tableHeader($me, $d = [])
    {
        $me->SetFillColor(220,220,220);
        // $me->SetTextColor(255,255,255);
        $this->pdf->SetFont('Arial','', 10);

        $h = 0.7;
        if ($d != []) {
            $me->Cell($this->width, $h, strtoupper($d['group_name'] . " » " . $d['account_name']) , 'LTBR', 0, 'C', 1);
            $me->Ln($h);
        }
        
        
        $me->Cell($this->widths['date'], $h, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        $me->Cell($this->widths['desc'], $h, 'DESKRIPSI' , 'LTBR', 0, 'C', 1);
        $me->Cell($this->widths['amount'], $h, 'DEBIT' , 'LTBR', 0, 'C', 1);
        $me->Cell($this->widths['amount'], $h, 'KREDIT' , 'LTBR', 0, 'C', 1);
        $me->Cell($this->widths['balance'], $h, 'SALDO' , 'LTBR', 0, 'C', 1);
        $me->Ln($h);
        

        $me->SetTextColor(0, 0, 0);
    }

    function endLine($me)
    {
        $me->Cell($this->width, 0.2, '' , 'T', 0, 'C', 0);
        $me->Ln(0.2);
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'account_id'=>isset($this->sys_input['account_id'])?$this->sys_input['account_id']:0,
            'account_ids'=>isset($this->sys_input['account_ids'])?$this->sys_input['account_ids']:''
        ];
        $r = $this->r_reportfinance->fin_002_1($prm);
        foreach ($r['records'] as $k => $v)
        {
            foreach ($v['details'] as $l => $w)
            {
                // if ($w->journal_type == 'J.11')
                //     $w->ledger_note = preg_replace("/\#[\/A-Z0-9]+/", "<a href='javascript:;' @click='select(props.item),detailRef(\$event,inv)'>$0</a>", $w->ledger_note);
            }
        }
        $this->sys_ok($r);
    }

    function excel()
    { 
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>1, 'limit'=>99999,
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'account_id'=>isset($this->sys_input['account_id'])?$this->sys_input['account_id']:0,
            'account_ids'=>isset($this->sys_input['account_ids'])?$this->sys_input['account_ids']:''
        ];
        $r = $this->r_reportfinance->fin_002_1($prm);

        $this->load->library("Excel");

        //
        $grand_total = ['debit'=>0, 'credit'=>0];
        $sub_total = 0;
        
        $filename = "laporan_buku_besar_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";

        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_fin_002.xls");

            // $data = [];
            $myLine = 8;
            $as = $objPHPExcel->getActiveSheet();
            // $g_total = [0, 0, 0, 0, 0, 0, 0];
            foreach ($r['records'] as $k => $v)
            {
                $this->copyRowFull($as, $as, 3, $myLine);
                $as->mergeCells("A{$myLine}:B{$myLine}");
                $as->mergeCells("C{$myLine}:E{$myLine}");

                $as->setCellValue("H{$myLine}", "DEBIT");
                $as->setCellValue("I{$myLine}", "KREDIT");
                $as->setCellValue("J{$myLine}", "SALDO");

                $as->setCellValue("A{$myLine}", $v['group_name']);
                $as->setCellValue("C{$myLine}", $v['account_code'] . "   " . $v['account_name']);
                // $as->setCellValue("H{$myLine}", $v['balance_open']);
                $as->getStyle('C'.$myLine)
                    ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);
                $myLine++;

                // SALDO AWAL
                $mapData = ['A' => '',
                                'B' => '',
                                'C' => '',
                                'D' => 'SALDO AWAL',
                                'E' => '',
                                'F' => '',
                                'G' => '',
                                'H' => '',
                                'I' => '',
                                'J' => '',
                                'K' => $v['balance_open']
                            ];
                foreach ($mapData as $m => $n) { $as->setCellValue($m.$myLine, "{$n}"); }
                $myLine++;

                foreach ($v['details'] as $l => $w)
                {
                    $no = $l + 1;
                    $mapData = ['A' => $no,
                                'B' => $w->journal_date,
                                'C' => $w->journal_number,
                                'D' => $w->journal_type_name,
                                'E' => $w->ledger_note,
                                'F' => $w->journal_ref_note,
                                'G' => ($w->journal_debit==0?$this->implodeAcc($w->adebit):$this->implodeAcc($w->acredit)),
                                'H' => implode(", ", json_decode($w->journal_tags)),
                                'I' => $w->journal_debit,
                                'J' => $w->journal_credit,
                                'K' => $w->balance
                            ];
                    foreach ($mapData as $m => $n)
                    {
                        $as->setCellValue($m.$myLine, "{$n}");
                    }
                    $grand_total['debit'] += ($w->journal_debit == null ? 0 : $w->journal_debit);
                    $grand_total['credit'] += ($w->journal_credit == null ? 0 : $w->journal_credit);

                    $myLine++;
                }
                
                $this->copyRowFull($as, $as, 5, $myLine);
                $mapData = ['K' => $v["balance_close"],
                            'I' => $v["total_debit"],
                            'J' => $v["total_credit"],
                        ];
                foreach ($mapData as $m => $n)
                {
                    $as->setCellValue($m.$myLine, "{$n}");
                }
                $as->setCellValue("C{$myLine}", "JUMLAH");

                $myLine++;
                $myLine++;
            }

            // GRAND TOTAL
            $this->copyRowFull($as, $as, 5, $myLine);
            $mapData = ['I' => $grand_total['debit'],
                        'J' => $grand_total['credit'],
                    ];
            foreach ($mapData as $m => $n)
            {
                $as->setCellValue($m.$myLine, "{$n}");
            }
            $as->setCellValue("C{$myLine}", "GRAND TOTAL");

            $as->removeRow(3,5);
            $as->setCellvalue("L2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("L3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");

            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Cache-Control: max-age=0");
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            ob_clean();
            $objWriter->save("php://output");

        }   
    }

    function excel2()
    { 
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>1, 'limit'=>99999,
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'account_id'=>isset($this->sys_input['account_id'])?$this->sys_input['account_id']:0,
            'account_ids'=>isset($this->sys_input['account_ids'])?$this->sys_input['account_ids']:''
        ];
        $r = $this->r_reportfinance->fin_002_1($prm);

        $this->load->library("Excel");

        //
        $grand_total = ['debit'=>0, 'credit'=>0];
        $sub_total = 0;
        
        $filename = "laporan_buku_besar_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";

        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_fin_002.xls");

            // $data = [];
            $myLine = 8;
            $as = $objPHPExcel->getActiveSheet();
            // $g_total = [0, 0, 0, 0, 0, 0, 0];
            foreach ($r['records'] as $k => $v)
            {
                $this->copyRowFull($as, $as, 3, $myLine);
                $as->mergeCells("A{$myLine}:B{$myLine}");
                $as->mergeCells("C{$myLine}:E{$myLine}");

                $as->setCellValue("H{$myLine}", "DEBIT");
                $as->setCellValue("I{$myLine}", "KREDIT");
                $as->setCellValue("J{$myLine}", "SALDO");

                $as->setCellValue("A{$myLine}", $v['group_name']);
                $as->setCellValue("C{$myLine}", $v['account_code'] . "   " . $v['account_name']);
                // $as->setCellValue("H{$myLine}", $v['balance_open']);
                $as->getStyle('C'.$myLine)
                    ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);
                $myLine++;

                // SALDO AWAL
                $mapData = ['A' => '',
                                'B' => '',
                                'C' => '',
                                'D' => 'SALDO AWAL',
                                'E' => '',
                                'F' => '',
                                'G' => '',
                                'H' => '',
                                'I' => '',
                                'J' => $v['balance_open']
                            ];
                foreach ($mapData as $m => $n) { $as->setCellValue($m.$myLine, "{$n}"); }
                $myLine++;

                foreach ($v['details'] as $l => $w)
                {
                    $no = $l + 1;
                    
                    $grand_total['debit'] += ($w->journal_debit == null ? 0 : $w->journal_debit);
                    $grand_total['credit'] += ($w->journal_credit == null ? 0 : $w->journal_credit);
                }
                
                $this->copyRowFull($as, $as, 5, $myLine);
                $mapData = ['J' => $v["balance_close"],
                            'H' => $v["total_debit"],
                            'I' => $v["total_credit"],
                        ];
                foreach ($mapData as $m => $n)
                {
                    $as->setCellValue($m.$myLine, "{$n}");
                }
                $as->setCellValue("C{$myLine}", "JUMLAH");

                $myLine++;
                $myLine++;
            }

            // GRAND TOTAL
            $this->copyRowFull($as, $as, 5, $myLine);
            $mapData = ['H' => $grand_total['debit'],
                        'I' => $grand_total['credit'],
                    ];
            foreach ($mapData as $m => $n)
            {
                $as->setCellValue($m.$myLine, "{$n}");
            }
            $as->setCellValue("C{$myLine}", "GRAND TOTAL");

            $as->removeRow(3,5);
            $as->setCellvalue("K2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("K3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");

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

    function implodeAcc($x) {
        $y = [];
        foreach ($x as $k => $v) $y[] = implode(' - ', $v);
        return implode(', ', $y);
    }
}
?>

<?php

// Report Laba Rugi
//

class One_fin_005 extends RPT_Controller
{
    var $report_code;
    var $balances;
    var $width;
    var $accs;
    var $font;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'FIN-005';
        $this->balances = ['asset.liquid'=>0, 'asset.fixed'=>0, 'asset.liability.current'=>0, 'asset.liability.longterm'=>0, 
            'expense_other'=>0, 'activa'=>0, 'liability'=>0, 'equity'=>0, 'profits'=>0];
        $this->accs = [
            'INCOME.SALES' => ['balance'=>0,'title'=>'PENJUALAN BERSIH'],
            'INCOME.HPP' => ['balance'=>0],
            'INCOME.EXPENSE' => ['balance'=>0,'title'=>'TOTAL BIAYA OPERASIONAL']
        ];
        $this->font = ['Arial', 10];

        $this->load->model("report/r_reportfinance");
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        $this->pdf->setRptTitle("LAPORAN NERACA");
        $this->pdf->setRptSubTitle("Per " . date("d M Y", strtotime($this->sys_input['edate'])));
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 10);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_fin_005( $this->input->get('sdate'), $this->input->get('edate') );
        // print_r($r);
        //
        $grand_total = 0;
        $sub_total = 0;
        $group_code = '';

        $nett_sales = 0;
        $operating_income = 0; 
        $other = 0;      
        $capital = 0; 

        if ($r)
        {
            // print_r($r);
            $data = $r;
            // $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.7);
            $this->pdf->AddPage('L', array(29.7,29.7));
            $this->width = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin;
            
            // $this->tableHeader($this->pdf);
            // $this->pdf->Cell($this->width, 0.7, 'PENJUALAN', 'B', 0, 'L', 0);
            $wNom = 3.5;
            $wName = $this->width - $wNom;
            $this->pdf->Ln(1);

            // foreach ($this->accs as $l => $w)
            // {
                // foreach ($data as $k => $v)
                // {
                //     if ($v['report_type'] == $l)
                //     {
                //         $this->pdf->SetStyle('B', false);
                //         $this->pdf->Cell($wName - 0.5, 1, $v['account_name'], '', 0, 'L', 0);
                //         $this->pdf->Cell(0.5, 1, 'Rp', '', 0, 'R', 0);
                //         $this->pdf->Cell($wNom, 1, $this->absx($v['journal_balance']), '', 0, 'R', 0);
                //         $this->pdf->Ln(1);

                //         $w['balance'] += $v['journal_balance'];
                //     }
                // }
                // if (isset($w['title']))
                // {
                //     $this->pdf->SetStyle('B', true);
                //     $this->pdf->Cell(1, 0.7, '', '', 0, 'L', 0);
                //     $this->pdf->Cell($wName - 1, 0.7, $w['title'], '', 0, 'L', 0);
                //     $this->pdf->Cell($wNom, 1, $this->absx($w['balance']), 'T', 0, 'R', 0);
                //     $this->pdf->Ln(1);
                // }

                // if ($l == 'INCOME.HPP')
                // {
                //     $this->pdf->SetStyle('B', true);
                //     $this->pdf->Cell(1, 0.7, '', '', 0, 'L', 0);
                //     $this->pdf->Cell($wName - 1, 0.7, 'LABA KOTOR', '', 0, 'L', 0);
                //     $this->pdf->Cell($wNom, 1, $this->absx($this->accs['INCOME.SALES']['balance']-$w['balance']), 'T', 0, 'R', 0);
                //     $this->pdf->Ln(1);
                // }
            // }


            // TITLE
            // $this->pdf->SetFillColor(222,222,222);
            $this->pdf->SetStyle('B', true);
            $this->pdf->Cell($this->width / 2 - 0.25, 1, 'AKTIVA', 'B', 0, 'C', 0);
            $this->pdf->Cell(0.5, 1, '', '', 0, 'C', 0);
            $this->pdf->Cell($this->width / 2 - 0.25, 1, 'KEWAJIBAN DAN EKUITAS', 'B', 0, 'C', 0);
            $this->pdf->Ln(1);
            $this->pdf->SetStyle('B', false);

            $top_y = $this->pdf->GetY();

            // ASET LANCAR
            $n = 0;
            $this->print_title(['title'=>'ASET LANCAR']);
            foreach ($data as $k => $v)
            {
                if ($v['report_type'] == 'BALANCE.ASSET.LIQUID') 
                {
                    $this->print_left_row($v, $n == 0 ? 'Rp' : '');
                    $this->balances['asset.liquid'] += $v['journal_balance'];
                    $this->balances['activa'] += $v['journal_balance'];
                    $this->balances['equity'] += $v['b_debit'] - $v['b_credit'];
                    // $this->capital += 
                    $n++;
                }
            }
            $this->print_total(['title'=>'TOTAL ASET LANCAR','amount'=>$this->balances['asset.liquid']], 'TB');

            // ASET TETAP
            $n = 0;
            $this->print_title(['title'=>'ASET TETAP']);
            foreach ($data as $k => $v)
            {
                if ($v['report_type'] == 'BALANCE.ASSET.FIXED') 
                {
                    $this->print_left_row($v, $n == 0 ? 'Rp' : '');
                    $this->balances['asset.fixed'] += $v['journal_balance'];
                    $this->balances['activa'] += $v['journal_balance'];
                    $this->balances['equity'] += $v['b_debit'] - $v['b_credit'];
                    $n++;
                }
            }
            $this->print_total(['title'=>'TOTAL ASET TETAP','amount'=>$this->balances['asset.fixed']], 'TB');


            $end_y = $this->pdf->GetY();

            // KEWAJiBAN LANCAR
            $this->pdf->SetY($top_y);
            $n = 0;
            $this->print_title(['title'=>'KEWAJIBAN LANCAR'], true);
            foreach ($data as $k => $v)
            {
                if ($v['report_type'] == 'BALANCE.LIABILITY.CURRENT') 
                {
                    $this->print_right_row($v, $n == 0 ? 'Rp' : '');
                    $this->balances['asset.liability.current'] += $v['journal_balance'];
                    $this->balances['liability'] += $v['journal_balance'];
                    $this->balances['equity'] -= $v['b_credit'] - $v['b_debit'];
                    $n++;
                }
            }
            $this->print_total(['title'=>'TOTAL KEWAJIBAN LANCAR','amount'=>$this->balances['asset.liability.current']], 'TB', true);

            // KEWAJiBAN JANGKA PANJANG
            $n = 0;
            $this->print_title(['title'=>'KEWAJIBAN JANGKA PANJANG'], true);
            foreach ($data as $k => $v)
            {
                if ($v['report_type'] == 'BALANCE.LIABILITY.LONGTERM') 
                {
                    $this->print_right_row($v, $n == 0 ? 'Rp' : '');
                    $this->balances['asset.liability.longterm'] += $v['journal_balance'];
                    $this->balances['liability'] += $v['journal_balance'];
                    $this->balances['equity'] -= $v['b_credit'] - $v['b_debit'];
                    $n++;
                }
            }
            $this->print_total(['title'=>'TOTAL KEWAJIBAN JANGKA PANJANG','amount'=>$this->balances['asset.liability.longterm']], 'TB', true);

            // EKUITAS
            $n = 0;
            $this->print_title(['title'=>'EKUITAS'], true);
            $this->balances['equity'] = 0;
            foreach ($data as $k => $v)
            {
                if ($v['report_type'] == 'BALANCE.EQUITY') 
                {
                    $this->print_right_row($v, $n == 0 ? 'Rp' : '');
                    $this->balances['liability'] += $v['journal_balance'];
                    $this->balances['equity'] += $v['journal_balance'];
                    $n++;
                }
            }

            $this->load->model('trans/t_journalclose');
            $profitloss = $this->t_journalclose->get_profit_loss(date('Y-12-31', strtotime($this->sys_input['edate'])));
            $pbal = $profitloss->credit - $profitloss->debit;

            $this->load->model('trans/t_journalyearly');
            $profitloss = $this->t_journalyearly->get(date('Y', strtotime($this->sys_input['edate'])), date('m', strtotime($this->sys_input['edate'])));
            $pbal = $profitloss->profit;


            // GET PROFITS
            $this->load->model('report/r_reportfinance');
            $balance = $this->r_reportfinance->get_balance_monthly(date('Y-m-d', strtotime($this->sys_input['sdate'])), date('Y-m-d', strtotime($this->sys_input['edate'])));
            // $this->balances['equity'] = $balance->balance;
            $this->balances['profits'] = $balance->profits;
            $pbal = $balance->profit;
            

            // $this->print_right_row(['account_name'=>'Modal Awal', 'journal_balance'=>$this->balances['equity'], 'parent_name'=>null]);
            if ($this->balances['profits'] > 0)
            {
                $this->print_right_row(['account_name'=>'Laba Rugi Bulan '.$balance->profits_period, 'journal_balance'=>$this->balances['profits'], 'parent_name'=>null]);
            }
                
            $this->print_right_row(['account_name'=>'Ikhtisar Laba Rugi', 'journal_balance'=>$pbal, 'parent_name'=>null]);
            // $this->balances['liability'] += $pbal;
            $this->balances['equity'] += $pbal;
            $this->balances['equity'] += $balance->profits;
            $this->balances['liability'] += $pbal;
            $this->balances['liability'] += $balance->profits;

            // $this->balances['liability'] += $this->balances['equity'];
            // $this->balances['liability'] += $this->balances['profits'];
            
            $this->print_total(['title'=>'TOTAL EKUITAS','amount'=>$this->balances['equity']], 'TB', true);

            // TOTAL
            if ($end_y > $this->pdf->GetY())
                $this->pdf->SetY($end_y);

            $this->setFontStyle('B');
            $this->pdf->Cell($this->width / 2 - 3.25, 0.7, 'TOTAL AKTIVA', 'TB', 0, 'L', 0);
            $this->pdf->Cell(3, 0.7, number_format($this->balances['activa']), 'TB', 0, 'R', 0);
            $this->pdf->Cell(0.5, 0.7, '', '', 0, 'L', 0);
            $this->pdf->Cell($this->width / 2 - 3.25, 0.7, 'TOTAL KEWAJIBAN DAN EKUITAS', 'TB', 0, 'L', 0);
            $this->pdf->Cell(3, 0.7, number_format($this->balances['liability']), 'TB', 0, 'R', 0);
            
            // // HPP
            // foreach ($data as $k => $v)
            // {
            //     if ($v['report_type'] == 'INCOME.HPP') 
            //     {
            //         $this->print_row($v);
            //         $this->balances['hpp'] += $v['journal_balance'];
            //     }
            // }
            // $nett_sales = $this->balances['sales']+$this->balances['hpp'];
            // $this->print_title(['title'=>'LABA KOTOR','amount'=>$nett_sales]);

            // // EXPENSE
            // foreach ($data as $k => $v)
            // {
            //     if ($v['report_type'] == 'INCOME.EXPENSE') 
            //     {
            //         $this->print_row($v);
            //         $this->balances['expense'] += $v['journal_balance'];
            //     }
            // }
            // $operating_income = $nett_sales + $this->balances['expense'];
            // $this->print_title(['title'=>'BIAYA OPERASIONAL','amount'=>$this->balances['expense']]);
            // $this->print_title(['title'=>'TOTAL PENDAPATAN DARI KEGIATAN OPERASIONAL','amount'=>$operating_income]);

            // // OTHER
            // $this->pdf->Ln(0.5);
            // foreach ($data as $k => $v)
            // {
            //     if ($v['report_type'] == 'INCOME.OTHER') 
            //     {
            //         $this->print_row($v);
            //         $this->balances['other'] += $v['journal_balance'];
            //     }
            // }
            // $this->print_title(['title'=>'PENDAPATAN DAN KEUNTUNGAN LAIN','amount'=>$this->balances['other']]);

            // // EXPENSE OTHER
            // foreach ($data as $k => $v)
            // {
            //     if ($v['report_type'] == 'INCOME.EXPENSE.OTHER') 
            //     {
            //         $this->print_row($v);
            //         $this->balances['expense_other'] += $v['journal_balance'];
            //     }
            // }
            // $this->print_title(['title'=>'BIAYA LAIN LAIN','amount'=>$this->balances['expense_other']], ($this->balances['expense_other'] > 0 ? 'T' : ''));
            
            // $other = $this->balances['other'] + $this->balances['expense_other'];
            // $this->print_title(['title'=>'LABA SEBELUM PAJAK PENGHASILAN','amount'=>$operating_income + $other]);
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

    function print_left_row($d, $currency = 'Rp')
    {
        $wNom = 3.0;
        $wName = $this->width / 2 - $wNom - 0.75;
        $this->setFontStyle('');
        $this->pdf->Cell(0.25, 0.7, '', '', 0, 'R', 0);
        $this->pdf->Cell($wName - 0.25, 0.7, $d['parent_name'] != null ? $d['parent_name'] : $d['account_name'], '', 0, 'L', 0);
        $this->pdf->Cell(0.5, 0.7, $currency, '', 0, 'R', 0);
        $this->pdf->Cell($wNom, 0.7, $this->absx($d['journal_balance']), '', 0, 'R', 0);
        $this->pdf->Ln(0.7);
    }

    function print_right_row($d, $currency = 'Rp')
    {
        $wNom = 3.0;
        $wName = $this->width / 2 - $wNom - 0.5;
        $this->setFontStyle('');
        $this->pdf->Cell($this->width / 2 + 0.5, 0.7, '', '', 0, 'R', 0);
        $this->pdf->Cell($wName - 0.5, 0.7, $d['parent_name'] != null ? $d['parent_name'] : $d['account_name'], '', 0, 'L', 0);
        $this->pdf->Cell(0.5, 0.7, $currency, '', 0, 'R', 0);
        $this->pdf->Cell($wNom, 0.7, $this->absx($d['journal_balance']), '', 0, 'R', 0);
        $this->pdf->Ln(0.7);
    }

    function print_title($d,  $right = false)
    {
        $wName = $this->width / 2 - 0.25;
        $this->setFontStyle('B');
        if ($right)
            $this->pdf->Cell($this->width / 2 + 0.25, 1, '', '', 0, 'L', 0);
        $this->pdf->Cell($wName, 1, $d['title'], '', 0, 'L', 0);
        // $this->pdf->Cell($wNom, 1, $this->absx($d['amount']), $border, 0, 'R', 0);
        $this->pdf->Ln(1);
    }

    function print_total($d, $border = 'T', $right = false)
    {
        $wNom = 3.0;
        $wName = $this->width / 2 - $wNom - ($right?0:0.25);
        $this->setFontStyle('B');
        if ($right)
            $this->pdf->Cell($this->width / 2 + 0.25, 1, '', '', 0, 'L', 0);
        $this->pdf->Cell(0.5, 1, '', '', 0, 'L', 0);
        $this->pdf->Cell($wName - ($right?0.75:0.5), 1, $d['title'], '', 0, 'L', 0);
        $this->pdf->Cell($wNom, 1, $this->absx($d['amount']), $border, 0, 'R', 0);
        $this->pdf->Ln(1);
    }

    function absx($x)
    {
        return ($x < 0 ? "(".number_format(abs($x)).")" : number_format($x));
    }

    function endLine($me, $w = 0)
    {
        $w == 0 ? $this->width : 0;
        $me->Cell($w, 0.2, '' , 'T', 0, 'C', 0);
        $me->Ln(0.2);
    }

    function setFontStyle($b)
    {
        $this->pdf->SetFont($this->font[0], $b, $this->font[1]);
    }

    function search()
    {
        $prm = [
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate']
        ];
        $r = $this->r_reportfinance->fin_005($prm);
        $this->sys_ok($r);
    }

    function excel()
    { 
        $this->load->model('report/r_report');
        $r = $this->r_reportfinance->fin_005( ['sdate'=>$this->sys_input['sdate'], 'edate'=>$this->sys_input['edate']] );

        $this->load->library("Excel");
        $filename = "laporan_neraca_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";

        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_fin_005.xls");

            // $data = [];
            $myLine = 3;
            $myLeftLine = $myLine;
            $myRightLine = $myLine;

            $as = $objPHPExcel->getActiveSheet();

            $total = [0, 0];

            $leftSide = [["id"=>'BALANCE.ASSET.LIQUID',"label"=>'ASET LANCAR',"details"=>[],"sub_total"=>0], 
                        ["id"=>'BALANCE.ASSET.FIXED',"label"=>'ASET TETAP', "details"=>[],"sub_total"=>0]];
            $rightSide = [["id"=>'BALANCE.LIABILITY.CURRENT',"label"=>'KEWAJIBAN LANCAR',"details"=>[],"sub_total"=>0], 
                        ["id"=>'BALANCE.LIABILITY.LONGTERM',"label"=>'KEWAJIBAN JANGKA PANJANG',"details"=>[],"sub_total"=>0], 
                        ["id"=>'BALANCE.EQUITY',"label"=>'EKUITAS',"details"=>[],"sub_total"=>0]];

            $cellStyle = [
                            'font' => ['bold' => true],
                            'borders' => ['top' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ],
                                        'bottom' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ]],
                        ];

            foreach ($leftSide as $k => $v)
            {
                $sub_total = 0;
                $as->setCellValue("A{$myLeftLine}", $v['label']);
                $myLeftLine++;

                foreach ($r as $l => $w)
                {
                    if ($v['id'] == $w['report_type'])
                    {
                        $as->setCellValue("A{$myLeftLine}", "  ".$w['account_name']);
                        $as->setCellValue("B{$myLeftLine}", $w['journal_balance']);

                        $sub_total += $w['journal_balance'];
                        $myLeftLine++;
                    }
                }

                $as->setCellValue("A{$myLeftLine}", 'TOTAL ' . $v['label']);
                $as->setCellValue("B{$myLeftLine}", $sub_total);
                $as->getStyle('A'.$myLeftLine.':B'.$myLeftLine)
                        ->applyFromArray($cellStyle);

                $myLeftLine++;
                $myLeftLine++;
                $total[0] += $sub_total;
            }

            foreach ($rightSide as $k => $v)
            {
                $sub_total = 0;
                $as->setCellValue("D{$myRightLine}", $v['label']);
                $myRightLine++;

                foreach ($r as $l => $w)
                {
                    if ($v['id'] == $w['report_type'])
                    {
                        $as->setCellValue("D{$myRightLine}", "  ".$w['account_name']);
                        $as->setCellValue("E{$myRightLine}", $w['journal_balance']);

                        $sub_total += $w['journal_balance'];
                        $myRightLine++;
                    }
                }

                $as->setCellValue("D{$myRightLine}", 'TOTAL ' . $v['label']);
                $as->setCellValue("E{$myRightLine}", $sub_total);
                $as->getStyle('D'.$myRightLine.':E'.$myRightLine)
                        ->applyFromArray($cellStyle);

                $myRightLine++;
                $myRightLine++;
                $total[1] += $sub_total;
            }

            $myLine = $myLeftLine >= $myRightLine ? $myLeftLine : $myRightLine;
            $as->setCellValue("A{$myLine}", 'TOTAL AKTIVA');
            $as->setCellValue("B{$myLine}", $total[0]);
                $as->getStyle('A'.$myLine.':B'.$myLine)
                        ->applyFromArray($cellStyle);

            $as->setCellValue("D{$myLine}", 'TOTAL KEWAJIBAN DAN EKUITAS');
            $as->setCellValue("E{$myLine}", $total[1]);
                $as->getStyle('D'.$myLine.':E'.$myLine)
                        ->applyFromArray($cellStyle);

            // foreach ($r as $k => $v)
            // {
            //     $this->copyRowFull($as, $as, 3, $myLine);
            //     $as->mergeCells("A{$myLine}:B{$myLine}");

            //     $as->setCellValue("A{$myLine}", $v['sub_title']);
            //     $as->getStyle('A'.$myLine.':C'.$myLine)
            //             ->applyFromArray(['font' => ['bold' => true]]);
            //     foreach ($v['details'] as $l => $w)
            //     {
            //         $myLine++;
            //         $mapData = ['A' => $w->account_code,
            //                     'B' => $w->account_name,
            //                     'C' => $w->journal_balance,
            //                 ];
            //         foreach ($mapData as $m => $n)
            //         {
            //             $as->setCellValue($m.$myLine, "{$n}");
            //         }
            //     }

            //     $myLine++;
            //     $this->copyRowFull($as, $as, 3, $myLine);
            //     $as->mergeCells("A{$myLine}:B{$myLine}");
                
            //     $as->setCellValue("A{$myLine}", 'TOTAL '.$v['sub_title']);
            //     $as->setCellValue("C{$myLine}", $v['sub_total']);

            //     $as->getStyle('A'.$myLine.':C'.$myLine)
            //             ->applyFromArray(
            //                 [
            //                     'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('rgb' => 'FFFFFF'), 'endcolor' => array('rgb' => 'FFFFFF')],
            //                     'font' => ['bold' => true],
            //                     'borders' => ['top' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ]],
            //                 ]
            //     );
            //     $myLine++;

            //     if ($v['sub_type'] == 'INCOME.SALES') $grossProfit += $v['sub_total'];
            //     if ($v['sub_type'] == 'INCOME.HPP') $grossProfit -= $v['sub_total'];
            //     if ($v['sub_type'] == 'INCOME.EXPENSE') $operationalProfit += $grossProfit - $v['sub_total'];
            //     if ($v['sub_type'] == 'INCOME.OTHER') $others += $v['sub_total'];
            //     if ($v['sub_type'] == 'INCOME.EXPENSE.OTHER') $others -= $v['sub_total'];
                
            //     if(array_search($v['sub_type'], ['INCOME.HPP', 'INCOME.EXPENSE', 'INCOME.EXPENSE.OTHER']) !== false)
            //     {
            //         $myLine++;
            //         $this->copyRowFull($as, $as, 4, $myLine);
            //         $as->mergeCells("A{$myLine}:B{$myLine}");
            //         $as->getStyle('A'.$myLine.':C'.$myLine)
            //             ->applyFromArray(
            //                 [
            //                     'font' => ['bold' => true],
            //                     'borders' => ['top' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ]],
            //                 ]
            //             );
            //     }
                    
            //     if ($v['sub_type'] == 'INCOME.HPP')
            //     {
            //         $as->setCellValue("A{$myLine}", 'LABA KOTOR');
            //         $as->setCellValue("C{$myLine}", $grossProfit);
            //         $myLine++;
            //     }

            //     if ($v['sub_type'] == 'INCOME.EXPENSE')
            //     {
            //         $as->setCellValue("A{$myLine}", 'LABA OPERASIONAL');
            //         $as->setCellValue("C{$myLine}", $operationalProfit);
            //         $myLine++;
            //     }

            //     if ($v['sub_type'] == 'INCOME.EXPENSE.OTHER')
            //     {
            //         $as->setCellValue("A{$myLine}", 'TOTAL PENDAPATAN DAN BEBAN LAIN');
            //         $as->setCellValue("C{$myLine}", $others);
            //         $myLine++;

            //         $nettProfit = $operationalProfit += $others;
            //         $this->copyRowFull($as, $as, 4, $myLine);
            //         $as->setCellValue("A{$myLine}", 'LABA BERSIH');
            //         $as->setCellValue("C{$myLine}", $nettProfit);
                    
            //         $as->getStyle('A'.$myLine.':C'.$myLine)
            //             ->applyFromArray(
            //                 [
            //                     'font' => ['bold' => true],
            //                     'borders' => ['top' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ],
            //                                 'bottom' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000'] ]],
            //                 ]
            //             );
            //         $myLine++;
            //     }
                

            //     $myLine++;
            // }

            // $as->getStyle('A'.$myLine)
            //     ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);

            // $as->removeRow(3,2);
            $as->setCellvalue("F2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("F3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");

            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Cache-Control: max-age=0");
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            ob_clean();
            $objWriter->save("php://output");

        }   
        // $this->sys_ok(["report_url"=>$this->REPORT_EXCEL_URL.$filename]);
    }
}
?>

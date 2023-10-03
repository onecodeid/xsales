<?php

// Report Laba Rugi
//

class One_fin_003 extends RPT_Controller
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
        $r = $this->r_report->one_fin_003( $this->input->get('sdate'), $this->input->get('edate') );
        // print_r($r);
        //
        $grand_total = 0;
        $sub_total = 0;
        $group_code = '';

        $nett_sales = 0;
        $operating_income = 0; 
        $other = 0;       
        if ($r)
        {
            // print_r($r);
            $data = $r;
            // $r = $r[0][0];
            $this->pdf->SetMargins(1.5, 1.5, 1.5);
            $this->pdf->AddPage('P', 'A4');
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



            // SALES
            foreach ($data as $k => $v)
            {
                if ($v['report_type'] == 'INCOME.SALES') 
                {
                    $this->print_row($v);
                    $this->balances['sales'] += $v['journal_balance'];
                }
            }
            $this->print_title(['title'=>'PENJUALAN BERSIH','amount'=>$this->balances['sales']]);
            
            // HPP
            foreach ($data as $k => $v)
            {
                if ($v['report_type'] == 'INCOME.HPP') 
                {
                    $this->print_row($v);
                    $this->balances['hpp'] += $v['journal_balance'];
                }
            }
            $nett_sales = $this->balances['sales']+$this->balances['hpp'];
            $this->print_title(['title'=>'LABA KOTOR','amount'=>$nett_sales]);

            // EXPENSE
            foreach ($data as $k => $v)
            {
                if ($v['report_type'] == 'INCOME.EXPENSE') 
                {
                    $this->print_row($v);
                    $this->balances['expense'] += $v['journal_balance'];
                }
            }
            $operating_income = $nett_sales + $this->balances['expense'];
            $this->print_title(['title'=>'BIAYA OPERASIONAL','amount'=>$this->balances['expense']]);
            $this->print_title(['title'=>'TOTAL PENDAPATAN DARI KEGIATAN OPERASIONAL','amount'=>$operating_income]);

            // OTHER
            $this->pdf->Ln(0.5);
            foreach ($data as $k => $v)
            {
                if ($v['report_type'] == 'INCOME.OTHER') 
                {
                    $this->print_row($v);
                    $this->balances['other'] += $v['journal_balance'];
                }
            }
            $this->print_title(['title'=>'PENDAPATAN DAN KEUNTUNGAN LAIN','amount'=>$this->balances['other']]);

            // EXPENSE OTHER
            foreach ($data as $k => $v)
            {
                if ($v['report_type'] == 'INCOME.EXPENSE.OTHER') 
                {
                    $this->print_row($v);
                    $this->balances['expense_other'] += $v['journal_balance'];
                }
            }
            $this->print_title(['title'=>'BIAYA LAIN LAIN','amount'=>$this->balances['expense_other']], ($this->balances['expense_other'] > 0 ? 'T' : ''));
            
            $other = $this->balances['other'] + $this->balances['expense_other'];
            $this->print_title(['title'=>'LABA SEBELUM PAJAK PENGHASILAN','amount'=>$operating_income + $other]);

            // SAVE
            $this->load->model('trans/t_journalclose');
            $this->t_journalclose->set_profit_loss(date('Y-m-t', strtotime($this->sys_input['edate'])), $operating_income + $other);

            $this->load->model('trans/t_journalyearly');
            $strtime = strtotime($this->sys_input['edate']);
            $this->t_journalyearly->set(date('Y', $strtime), date('m', $strtime), ['profit'=>$operating_income + $other]);
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
        $this->pdf->Cell($wName - 0.5, 1, $d['account_name'], '', 0, 'L', 0);
        $this->pdf->Cell(0.5, 1, 'Rp', '', 0, 'R', 0);
        $this->pdf->Cell($wNom, 1, $this->absx($d['journal_balance']), '', 0, 'R', 0);
        $this->pdf->Ln(1);
    }

    function print_title($d, $border = 'T')
    {
        $wNom = 3.5;
        $wName = $this->width - $wNom;
        $this->pdf->SetStyle('B', true);
        $this->pdf->Cell(1, 1, '', '', 0, 'L', 0);
        $this->pdf->Cell($wName - 1, 1, $d['title'], '', 0, 'L', 0);
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
}
?>

<?php

// Report Perubahan Modal
//

class One_fin_009 extends RPT_Controller
{
    var $report_code;
    var $balances;
    var $width;
    var $accs;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'FIN-009';

        $this->load->model("report/r_reportfinance");
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        $this->pdf->setRptTitle("LAPORAN PERUBAHAN MODAL");
        $this->pdf->setRptSubTitle("Per " . date("d M Y", strtotime($this->sys_input['edate'])));
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_fin_008( $this->input->get('sdate'), $this->input->get('edate') );
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



            // AKTIVITAS OPERASIONAL
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
            $this->t_journalclose->set_profit_loss(date('Y-12-31', strtotime($this->sys_input['edate'])), $operating_income + $other);
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

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>1,
            'sdate'=>date('Y-m-d', strtotime($this->sys_input['sdate'])),
            'edate'=>date('Y-m-d', strtotime($this->sys_input['edate']))
        ];
        $r = $this->r_reportfinance->fin_009($prm);
        $this->sys_ok($r);
    }

    function search_detail()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'account_report_id'=>$this->sys_input['account_report_id']
        ];
        $r = $this->r_reportfinance->fin_008_detail($prm);
        $this->sys_ok($r);
    }

    function excel()
    { 
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>1,
            'sdate'=>date('Y-m-d', strtotime($this->sys_input['sdate'])),
            'edate'=>date('Y-m-d', strtotime($this->sys_input['edate']))
        ];
        $r = $this->r_reportfinance->fin_009($prm);

        $this->load->library("Excel");
        $filename = "laporan_perubahan_modal_".date('d.m.Y', strtotime($this->input->get('sdate'))).".xls";

        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_fin_009.xls");

            $myLine = 8;
            $as = $objPHPExcel->getActiveSheet();
            
            $v = $r['records'];

            // $internalFormula = 
            // PHPExcel_Calculation::getInstance()->translateFormulaToEnglish("=SUM(C3:C4)");

            $as->setCellValue("D2", '=DATE'.date('(Y,1,1)', strtotime($this->input->get('sdate'))));
            $as->setCellValue("D3", '=DATE'.date('(Y,m,d)', strtotime($this->input->get('edate'))));
            $as->setCellValue("C3", round($v->balance_start));
            $as->setCellValue("C4", round($v->profit));
            $as->setCellValue("C5", "=C3+C4");
            $as->setCellValue("C7", $v->prive);
            $as->setCellValue("C8", "=C5-C7");

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

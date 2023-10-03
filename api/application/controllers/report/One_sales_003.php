<?php

// Report Fee / Komisi Per Admin
//

class One_sales_003 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'SALES-003';
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Rekapitulasi Omzet Sales');
        $this->pdf->setRptSubtitle('Periode ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' s/d ' .
            date('d/m/Y', strtotime($this->input->get('edate'))));
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_sales_003( 
            date('Y-m-d', strtotime($this->input->get('sdate'))), 
            date('Y-m-d', strtotime($this->input->get('edate'))) );
        // print_r($r);
        //
        $ach_total = 0;
        $target_total = 0;
        $level = 0;

        if ($r)
        {
            $data = $r[0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');

            $hy = $this->pdf->GetY();
            // $this->my_header($this, 
            //     'Laporan Omzet Per Sales', 
            //     'Periode : ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' - ' .  date('d/m/Y', strtotime($this->input->get('edate'))));

            $this->tableHeader($this->pdf);
            $this->pdf->SetFillColor(255,255,255);
            $this->pdf->SetTextColor(0,0,0);
            $this->pdf->SetFont('Arial','', 9);
            
            foreach ($data as $k => $v)
                $ach_total += $v['ach'];

            $achs = [];
            $hpps = [];
            foreach ($data as $k => $v)
            {
                $this->pdf->Cell(6.5, 0.7, $v['staff_name'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(2.5, 0.7, number_format($v['target']), 'LB', 0, 'R', 1);
                $this->pdf->Cell(2.5, 0.7, number_format($v['ach']), 'LB', 0, 'R', 1);
                $this->pdf->Cell(2.5, 0.7, number_format($v['target'] - $v['ach']), 'LB', 0, 'R', 1);
                $this->pdf->Cell(2.5, 0.7, number_format($v['ach']*100/$ach_total, 2) . ' %', 'LB', 0, 'R', 1);
                $this->pdf->Cell(2.5, 0.7, number_format($v['percentage'], 2) . ' %', 'LBR', 0, 'R', 1);
                $this->pdf->Ln(0.7);

                $achs[$v['staff_name']] = $v['ach'];
                $hpps[$v['staff_name']] = $v['hpp'];
                $target_total += $v['target'];
            }

            $this->pdf->SetFont('Arial','B', 9);
            $this->pdf->Cell(6.5, 0.7, 'T O T A L', 'TLB', 0, 'L', 1);
            $this->pdf->Cell(2.5, 0.7, number_format($target_total), 'TLB', 0, 'R', 1);
            $this->pdf->Cell(2.5, 0.7, number_format($ach_total), 'TLB', 0, 'R', 1);
            $this->pdf->Cell(2.5, 0.7, number_format($target_total - $ach_total), 'TLB', 0, 'R', 1);
            $this->pdf->Cell(2.5, 0.7, '', 'TLB', 0, 'R', 1);
            $this->pdf->Cell(2.5, 0.7, number_format($ach_total * 100 / $target_total, 2) . ' %', 'TLBR', 0, 'R', 1);

            $rows = [
                ['name'=>'Lead', 'code'=>'LEAD'], 
                ['name'=>'Conversion', 'code'=>'CONVERT'], 
                ['name'=>'Customer', 'code'=>'CUSTOMER'], 
                ['name'=>'Customer Freq', 'code'=>'FREQ'], 
                ['name'=>'Customer Value per Trans', 'code'=>'VALUE'], 
                ['name'=>'Sales', 'code'=>'SALES'], 
                ['name'=>'Hpp', 'code'=>'HPP'], 
                ['name'=>'Laba Kotor', 'code'=>'PROFIT']];
            $ops = ['X', '=', 'X', 'X', '=', '-', '='];
            
            foreach ($r[1] as $l => $w)
                $rows[0][$w['staff_name']] = $w['offer_cnt'];
            foreach ($r[1] as $l => $w)
                $rows[1][$w['staff_name']] = $w['sales_cnt'] * 100 / ($w['offer_cnt']==0?1:$w['offer_cnt']);
            foreach ($r[1] as $l => $w)
                $rows[2][$w['staff_name']] = $w['sales_cnt'];
            foreach ($r[1] as $l => $w)
                $rows[3][$w['staff_name']] = $w['sales_cnt'] / ($w['customer_cnt']==0?1:$w['customer_cnt']);
            foreach ($r[1] as $l => $w)
                $rows[4][$w['staff_name']] = (isset($achs[$w['staff_name']])?$achs[$w['staff_name']]:0) / ($w['sales_cnt']==0?1:$w['sales_cnt']);
            foreach ($r[1] as $l => $w)
                $rows[5][$w['staff_name']] = isset($achs[$w['staff_name']])?$achs[$w['staff_name']]:0;
            foreach ($r[1] as $l => $w)
                $rows[6][$w['staff_name']] = isset($hpps[$w['staff_name']])?$hpps[$w['staff_name']]:0;
            foreach ($r[1] as $l => $w)
                $rows[7][$w['staff_name']] = $rows[5][$w['staff_name']] - $rows[6][$w['staff_name']];
            
            $this->pdf->Ln(1);
            $this->tableHeader2($this->pdf, $r[1]);
            $this->pdf->SetFillColor(255,255,255);
            $this->pdf->SetTextColor(0,0,0);
            $this->pdf->SetFont('Arial','', 9);
            
            foreach ($rows as $k => $v)
            {
                $this->pdf->Cell(5, 0.7, $v['name'], 'LB', 0, 'C', 1);
                foreach ($v as $l => $w)
                    if ($l != 'name' && $l != 'code') {
                        if ($v['code'] == 'CONVERT')
                            $this->pdf->Cell(3, 0.7, number_format($w, 2) . ' %', 'LBR', 0, 'R', 1);
                        else if ($v['code'] == 'FREQ')
                            $this->pdf->Cell(3, 0.7, number_format($w, 2), 'LBR', 0, 'R', 1);
                        else if ($v['code'] == 'SALES' || $v['code'] == 'VALUE' || 
                                    $v['code'] == 'HPP' || $v['code'] == 'PROFIT') {
                            $this->pdf->Cell(0.5, 0.7, 'Rp', 'LB', 0, 'L', 1);
                            $this->pdf->Cell(2.5, 0.7, number_format($w), 'BR', 0, 'R', 1);
                        }
                        else
                            $this->pdf->Cell(3, 0.7, number_format($w), 'LBR', 0, 'R', 1);
                    }
                        
                $this->pdf->Ln(0.7);

                $this->pdf->Cell(5, 0.7, isset($ops[$k])?$ops[$k]:'', 'LB', 0, 'C', 1);
                foreach ($v as $l => $w)
                    if ($l != 'name' && $l != 'code')
                        $this->pdf->Cell(3, 0.7, '', 'LBR', 0, 'R', 1);
                // $this->pdf->Cell(14, 0.7, '', 'TRLB', 0, 'C', 1);
                $this->pdf->Ln(0.7);
            }
            // $this->pdf->SetFillColor(222,222,222);
            // $this->pdf->SetTextColor(0,0,0);
            // $this->pdf->Cell(6, 0.7, 'GRAND TOTAL', 'LB', 0, 'C', 1);
            // $this->pdf->Cell(5, 0.7, number_format($grand_total), 'LBR', 0, 'R', 1);

            // $this->pdf->Ln(1);

            // $data = $r[1];
            
            // $n = 0;
            // foreach ($data as $k => $v)
            // {
            //     if ($level != $v['level_id'])
            //     {
            //         if ($level != 0)
            //         {
            //             // SUB TOTAL
            //             $this->print_total($this, $sub_total);
            //         }

            //         $level = $v['level_id'];
            //         $n = 0;
            //         $sub_total = 0;
                    
            //         $this->tableHeader2($this->pdf, $v);
            //         $this->pdf->SetFillColor(255,255,255);
            //         $this->pdf->SetTextColor(0,0,0);
            //         $this->pdf->SetFont('Arial','', 9);
            //     }

                

            //     $this->pdf->Cell(1, 0.7, ++$n, 'LB', 0, 'C', 1);
            //     $this->pdf->Cell(8, 0.7, $v['customer_name'], 'LB', 0, 'L', 1);
            //     $this->pdf->Cell(5, 0.7, $v['level_name'], 'LB', 0, 'L', 1);
            //     $this->pdf->Cell(5, 0.7, number_format($v['omzet']), 'LBR', 0, 'R', 1);
            //     $this->pdf->Ln(0.7);

            //     $grand_total += $v['omzet'];
            //     $sub_total += $v['omzet'];
            // }
            // // SUB TOTAL
            // $this->print_total($this, $sub_total);
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

  function print_total($me, $total)
  {
    $me->pdf->SetFillColor(222,222,222);
    $me->pdf->SetTextColor(0,0,0);
    $me->pdf->Cell(14, 0.7, 'SUB TOTAL', 'LB', 0, 'C', 1);
    $me->pdf->Cell(5, 0.7, number_format($total), 'LBR', 0, 'R', 1);
    $me->pdf->Ln(1);

    // $me->pdf->Ln(0.25);
    // $me->pdf->Cell(0.5, 0.7, '', '', 0);
    // $me->pdf->Cell(12.5, 0.7, 'TOTAL', 'LBT', 0, 'C', 1);
    // $me->pdf->Cell(6, 0.7, number_format($total, 0), 'RBTL', 0, 'R', 1);
    // $me->pdf->Ln(0.7);
  }

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
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $this->pdf->SetFont('Arial','', 10);
        $me->Cell(6.5, 1, 'SALES' , 'LTBR', 0, 'L', 1);
        $me->Cell(2.5, 1, 'TARGET' , 'LTBR', 0, 'R', 1);
        $me->Cell(2.5, 1, 'ACH' , 'LTBR', 0, 'R', 1);
        $me->Cell(2.5, 1, 'GAP' , 'LTBR', 0, 'R', 1);
        $me->Cell(2.5, 1, 'KONTRIBUSI' , 'LTBR', 0, 'R', 1);
        $me->Cell(2.5, 1, 'AVERAGE' , 'LTBR', 0, 'R', 1);

        $me->Ln(1);
    }

    function tableHeader2($me, $data)
    {
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $this->pdf->SetFont('Arial','', 10);
        $me->Cell(5, 1, 'DESCRIPTION' , 'LTBR', 0, 'L', 1);
        foreach ($data as $k => $v)
            $me->Cell(3, 1, $v['staff_code'] , 'LTBR', 0, 'R', 1);

        $me->Ln(1);
    }
}
?>

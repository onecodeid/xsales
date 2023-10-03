<?php

// Report Omzet Customer Last 3 Month vs Target
//

class One_sales_007 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'SALES-007';
    }

    function index() {
        $this->pdf = new FPDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        // $this->pdf->header_func = "my_header";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_sales_007( $this->input->get('date'), $this->input->get('level_id') );
        // print_r($r);
        //
        $grand_total = 0;
        $sub_total = 0;

        if ($r)
        {
            $data = [];
            if (isset($r[1]))
                $data = $r[1];
            $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');

            $hy = $this->pdf->GetY();
            // $this->pdf->Image(base_url() . '/assets/images/logo-1-1.png', 0.8, 0.7, 3);
            $this->my_header($this, 
                'Laporan Omzet Customer 3 Bulan Terakhir', 
                '', 'L');

            
            $this->tableHeader($this->pdf, $r);

            $this->pdf->SetFillColor(255,255,255);
            $this->pdf->SetTextColor(0,0,0);
            $this->pdf->SetFont('Arial','', 9);
            foreach ($data as $k => $v)
            {
                $this->pdf->Cell(1, 0.7, $k+1, 'LB', 0, 'C', 1);
                $this->pdf->Cell(8.5, 0.7, $v['M_CustomerName'].', '.$v['M_CityName'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(2, 0.7, $v['M_CustomerLevelName'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(3, 0.7, number_format($v['F_TargetOmzetAmount']), 'LB', 0, 'R', 1);

                if ($v['month1'] >= $v['F_TargetOmzetAmount']) $this->pdf->SetTextColor(0,0,0);
                else $this->pdf->SetTextColor(255,0,0);
                $this->pdf->Cell(3, 0.7, number_format($v['month1']), 'LB', 0, 'R', 1);                
                $this->pdf->Cell(1.5, 0.7, round($v['month1'] * 100 / $v['F_TargetOmzetAmount'], 1) . ' %' , 'LB', 0, 'R', 1);

                if ($v['month2'] >= $v['F_TargetOmzetAmount']) $this->pdf->SetTextColor(0,0,0);
                else $this->pdf->SetTextColor(255,0,0);
                $this->pdf->Cell(3, 0.7, number_format($v['month2']), 'LB', 0, 'R', 1);
                $this->pdf->Cell(1.5, 0.7, round($v['month2'] * 100 / $v['F_TargetOmzetAmount'], 1) . ' %', 'LB', 0, 'R', 1);

                if ($v['month3'] >= $v['F_TargetOmzetAmount']) $this->pdf->SetTextColor(0,0,0);
                else $this->pdf->SetTextColor(255,0,0);
                $this->pdf->Cell(3, 0.7, number_format($v['month3']), 'LB', 0, 'R', 1);
                $this->pdf->Cell(1.5, 0.7, round($v['month3'] * 100 / $v['F_TargetOmzetAmount'], 1) . ' %', 'LB', 0, 'R', 1);
                // $this->pdf->Cell(2, 0.7, number_format($v['item_price']-$v['item_disc_total']), 'LB', 0, 'R', 1);
                // $this->pdf->Cell(1, 0.7, number_format($v['item_qty']), 'LB', 0, 'R', 1);
                // $this->pdf->Cell(3, 0.7, number_format($v['item_subtotal']), 'LBR', 0, 'R', 1);
                $this->pdf->Ln(0.7);

                $this->pdf->SetTextColor(0,0,0);
                // $grand_total += $v['item_subtotal'];
            }

            // $this->pdf->SetFillColor(222,222,222);
            // $this->pdf->SetTextColor(0,0,0);
            // $this->pdf->Cell(16, 0.7, 'GRAND TOTAL', 'LB', 0, 'C', 1);
            // $this->pdf->Cell(3, 0.7, number_format($grand_total), 'LBR', 0, 'R', 1);
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

    function tableHeader($me, $data)
    {
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $this->pdf->SetFont('Arial','', 10);
        $me->Cell(1, 1, 'NO' , 'LTBR', 0, 'C', 1);
        $me->Cell(8.5, 1, 'CUSTOMER' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'LEVEL' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'TARGET' , 'LTBR', 0, 'R', 1);
        $me->Cell(3, 1, strtoupper($data['month1']) , 'LTBR', 0, 'R', 1);
        $me->Cell(1.5, 1, '%' , 'LTBR', 0, 'R', 1);
        $me->Cell(3, 1, strtoupper($data['month2']) , 'LTBR', 0, 'R', 1);
        $me->Cell(1.5, 1, '%' , 'LTBR', 0, 'R', 1);
        $me->Cell(3, 1, strtoupper($data['month3']) , 'LTBR', 0, 'R', 1);
        $me->Cell(1.5, 1, '%' , 'LTBR', 0, 'R', 1);

        $me->Ln(1);
    }
}
?>

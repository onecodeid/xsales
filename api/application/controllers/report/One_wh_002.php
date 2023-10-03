<?php

// Report Fee / Komisi Per Admin
//

class One_wh_002 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'WH-002';
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
        $r = $this->r_report->one_wh_002( $this->input->get('sdate'), $this->input->get('edate'), $this->input->get('exp_id') );
        // print_r($r);
        //
        $grand_total = 0;
        $sub_total = 0;

        if ($r)
        {
            $data = $r[0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('P', 'A4');

            $hy = $this->pdf->GetY();
            // $this->pdf->Image(base_url() . '/assets/images/logo-1-1.png', 0.8, 0.7, 3);
            $this->my_header($this, 
                'LOGBOOK EKSPEDISI GUDANG ZALFA', 
                'Periode : ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' - ' .  date('d/m/Y', strtotime($this->input->get('edate'))));

            
            $this->tableHeader($this->pdf);

            $this->pdf->SetFillColor(255,255,255);
            $this->pdf->SetTextColor(0,0,0);
            $this->pdf->SetFont('Arial','', 9);
            foreach ($data as $k => $v)
            {
                $y = $this->pdf->GetY();
                $this->pdf->Cell(10, 0.7, '', '', 0, 'L', 1);
                $this->pdf->MultiCell(4, 0.7, $v['M_ExpeditionName'].' - '.$v['L_SoExpService'], 'LB', 'C', 1);
                $d = $this->pdf->GetY() - $y;

                $this->pdf->SetY($y);
                $this->pdf->SetX(0.7);
                $this->pdf->Cell(1, $d, $k+1, 'LB', 0, 'C', 1);
                $this->pdf->Cell(4, $d, $v['M_CustomerName'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(2.5, $d, $v['L_SoNumber'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(2.5, $d, $v['admin_username'], 'LB', 0, 'L', 1);

                $this->pdf->Cell(4, $d, '', '', 0, 'C', 0);
                $this->pdf->Cell(1.5, $d, '', 'LB', 0, 'R', 1);
                $this->pdf->Cell(2, $d, '', 'LBR', 0, 'R', 1);
                $this->pdf->Cell(1.5, $d, '', 'LBR', 0, 'R', 1);
                $this->pdf->Ln($d);

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

    function tableHeader($me)
    {
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $this->pdf->SetFont('Arial','', 10);
        $me->Cell(1, 1, 'NO' , 'LTBR', 0, 'C', 1);
        $me->Cell(4, 1, 'CUSTOMER' , 'LTBR', 0, 'C', 1);
        $me->Cell(2.5, 1, 'NO ORDER' , 'LTBR', 0, 'C', 1);
        $me->Cell(2.5, 1, 'ADMIN' , 'LTBR', 0, 'C', 1);
        $me->Cell(4, 1, 'EKSPEDISI' , 'LTBR', 0, 'C', 1);
        $me->Cell(1.5, 1, 'CEK' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'TTD PIC' , 'LTBR', 0, 'C', 1);
        $me->Cell(1.5, 1, 'KODE' , 'LTBR', 0, 'C', 1);

        $me->Ln(1);
    }
}
?>

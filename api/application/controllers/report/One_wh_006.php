<?php

// Report Fee / Komisi Per Admin
//

class One_wh_006 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'WH-005';
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
        $r = $this->r_report->one_wh_006( $this->input->get('opnameid') );
        // print_r($r);
        //
        $grand_total = 0;
        $grand_total_qty = 0;
        $grand_total_trx = 0;

        $total = 0;
        $total_qty = 0;
        $total_trx = 0;

        $sub_total = 0;
        $is_packet = "N";

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
                'Stok Opname', 
                'Tanggal : '.date('d-m-Y', strtotime($r['opname_date'])).'   Nomor : '.$r['opname_number'], 'L');

            
            $this->tableHeader($this->pdf);

            $this->pdf->SetFillColor(255,255,255);
            $this->pdf->SetTextColor(0,0,0);
            $this->pdf->SetFont('Arial','', 9);
            foreach ($data as $k => $v)
            {
                    if ($this->pdf->GetY() > 19.5)
                    {
                        $this->pdf->AddPage('L', 'A4');
                        $this->my_header($this, 
                            'Stok Opname', 
                            'Tanggal : '.date('d-m-Y', strtotime($r['opname_date'])).'   Nomor : '.$r['opname_number'], 'L');
                        $this->tableHeader($this->pdf);

                        $this->pdf->SetFillColor(255,255,255);
                        $this->pdf->SetTextColor(0,0,0);
                        $this->pdf->SetFont('Arial','', 9);
                    }
                    

                    // $this->pdf->SetFillColor(222,222,222);
                    // $this->pdf->SetTextColor(0,0,0);
                    $this->pdf->Cell(1, 0.7, $k+1, 'LB', 0, 'L', 1);
                    $this->pdf->Cell(8, 0.7, $v['item_name'], 'LB', 0, 'L', 1);
                    $this->pdf->Cell(2, 0.7, number_format($v['qty_before']), 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(3, 0.7, number_format($v['nominal_before']), 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(2, 0.7, number_format($v['qty_current']), 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(3, 0.7, number_format($v['nominal_current']), 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(2, 0.7, number_format($v['qty_adjust']), 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(3, 0.7, number_format($v['nominal_adjust']), 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(4, 0.7, $v['adjust_note'], 'LBR', 0, 'L', 1);
                    $this->pdf->Ln(0.7);
                    
                    // $this->tableHeader($this->pdf);

                    $this->pdf->SetFillColor(255,255,255);
                    $this->pdf->SetTextColor(0,0,0);
                    $this->pdf->SetFont('Arial','', 9);

                    $total = 0;
                    $total_qty = 0;
                    $total_trx = 0;
                

                // $this->pdf->Cell(1, 0.7, $k+1, 'LB', 0, 'C', 1);
                // // $this->pdf->Cell(2, 0.7, date('d-m-Y', strtotime($v['so_date'])), 'LB', 0, 'C', 1);
                // $this->pdf->Cell(9, 0.7, $v['item_name'], 'LB', 0, 'L', 1);
                // // $this->pdf->Cell(5, 0.7, $v['total'], 'LB', 0, 'L', 1);
                // $this->pdf->Cell(3, 0.7, number_format($v['total_trx']), 'LB', 0, 'R', 1);
                // $this->pdf->Cell(3, 0.7, number_format($v['total_qty']), 'LB', 0, 'R', 1);
                // $this->pdf->Cell(3, 0.7, number_format($v['total']), 'LBR', 0, 'R', 1);
                // $this->pdf->Ln(0.7);

                // $grand_total += $v['total'];
                // $grand_total_qty += $v['total_qty'];
                // $grand_total_trx += $v['total_trx'];

                // $total += $v['total'];
                // $total_qty += $v['total_qty'];
                // $total_trx += $v['total_trx'];
            }

            // $this->pdf->SetFillColor(222,222,222);
            // $this->pdf->SetTextColor(0,0,0);
            // $this->pdf->Cell(10, 0.7, 'TOTAL', 'LB', 0, 'C', 1);
            // $this->pdf->Cell(3, 0.7, number_format($total_trx), 'LBR', 0, 'R', 1);
            // $this->pdf->Cell(3, 0.7, number_format($total_qty), 'LBR', 0, 'R', 1);
            // $this->pdf->Cell(3, 0.7, number_format($total), 'LBR', 0, 'R', 1);
            // $this->pdf->Ln(1);

            // $this->pdf->SetFillColor(222,222,222);
            // $this->pdf->SetTextColor(0,0,0);
            // $this->pdf->Cell(10, 0.7, 'GRAND TOTAL', 'TLB', 0, 'C', 1);
            // $this->pdf->Cell(3, 0.7, number_format($grand_total_trx), 'LBRT', 0, 'R', 1);
            // $this->pdf->Cell(3, 0.7, number_format($grand_total_qty), 'LBRT', 0, 'R', 1);
            // $this->pdf->Cell(3, 0.7, number_format($grand_total), 'LBRT', 0, 'R', 1);
        }
        $this->pdf->Ln(0.5);
        $this->pdf->Cell(23, 0.7, '', '', 0, 'L', 1);
        $this->pdf->Cell(5, 0.7, date('d-m-Y', strtotime($r['opname_date'])), '', 0, 'C', 1);
        $this->pdf->Ln(0.5);
        $this->pdf->Cell(23, 0.7, '', '', 0, 'L', 1);
        $this->pdf->Cell(5, 0.7, 'Petugas,', '', 0, 'C', 1);
        $this->pdf->Ln(1.5);
        $this->pdf->Cell(23, 0.7, '', '', 0, 'L', 1);
        $this->pdf->Cell(5, 0.7, $r['user_fullname'], '', 0, 'C', 1);

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
        $me->Cell(1, 2*0.7, 'NO' , 'LTBR', 0, 'C', 1);
        // $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(8, 2*0.7, 'NAMA ITEM' , 'LTBR', 0, 'C', 1);
        $me->Cell(5, 1*0.7, 'STOK SISTEM' , 'LTBR', 0, 'C', 1);
        $me->Cell(5, 1*0.7, 'STOK FISIK / GUDANG' , 'LTBR', 0, 'C', 1);
        $me->Cell(5, 1*0.7, 'SELISIH STOK' , 'LTBR', 0, 'C', 1);
        $me->Cell(4, 2*0.7, 'CATATAN' , 'LTBR', 0, 'C', 1);
        $me->Ln(1*0.7);
        $me->Cell(9, 1*0.7, '' , '', 0, 'C', 0);
        $me->Cell(2, 1*0.7, 'QTY' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1*0.7, 'NOMINAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1*0.7, 'QTY' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1*0.7, 'NOMINAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1*0.7, 'QTY' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1*0.7, 'NOMINAL' , 'LTBR', 0, 'C', 1);

        $me->Ln(1*0.7);
    }
}
?>

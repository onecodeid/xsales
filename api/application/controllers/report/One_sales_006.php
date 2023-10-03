<?php

// Report Fee / Komisi Per Admin
//

class One_sales_006 extends RPT_Controller
{
    var $report_code;
    var $category;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'SALES-006';
    }

    function index() {
        $this->pdf = new FPDF("L","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        // $this->pdf->header_func = "my_header";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_sales_006( $this->input->get('sdate'), $this->input->get('edate'));
        
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
            $data = $r[0];
            $category = $r[1];
            $this->category = $category;
            
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');

            $hy = $this->pdf->GetY();
            // $this->pdf->Image(base_url() . '/assets/images/logo-1-1.png', 0.8, 0.7, 3);
            $this->my_header($this, 
                'Laporan Omzet Per Produk Per Kategori', 
                'Periode : ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' - ' .  date('d/m/Y', strtotime($this->input->get('edate'))),
                'L');
            
            $this->tableHeader($this->pdf);

            $this->pdf->SetFillColor(255,255,255);
            $this->pdf->SetTextColor(0,0,0);
            $this->pdf->SetFont('Arial','', 9);
            $num_col_width = 2.5;
            foreach ($data as $k => $v)
            {
                $this->pdf->Cell(1, 0.7, $k+1, 'LBR', 0, 'C', 1);
                $this->pdf->Cell(8, 0.7, $v['item_name'], 'LBR', 0, 'L', 1);
                foreach ($category as $l => $w)
                {
                    $nominal = $v['category_id'] == $w['category_id'] ? $v['total'] : 0;
                    $this->pdf->Cell($num_col_width, 0.7, number_format($nominal), 'LBR', 0, 'R', 1);
                    $category[$l]['total'] += $nominal;
                }
                $grand_total += $v['total'];
                $this->pdf->Ln(0.7);
                // if ($is_packet != $v['is_packet'] && (!$this->input->get('type') || $this->input->get('type') == 'A'))
                // {
                    // $is_packet = $v['is_packet'];

                    // $this->pdf->SetFillColor(222,222,222);
                    // $this->pdf->SetTextColor(0,0,0);
                    // $this->pdf->Cell(10, 0.7, 'TOTAL', 'LB', 0, 'C', 1);
                    // $this->pdf->Cell(3, 0.7, number_format($total_trx), 'LBR', 0, 'R', 1);
                    // $this->pdf->Cell(3, 0.7, number_format($total_qty), 'LBR', 0, 'R', 1);
                    // $this->pdf->Cell(3, 0.7, number_format($total), 'LBR', 0, 'R', 1);
                    // $this->pdf->Ln(1);
                    
                    // $this->tableHeader($this->pdf);

                    // $this->pdf->SetFillColor(255,255,255);
                    // $this->pdf->SetTextColor(0,0,0);
                    // $this->pdf->SetFont('Arial','', 9);

                    // $total = 0;
                    // $total_qty = 0;
                    // $total_trx = 0;
                // }

                
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

            $this->pdf->SetFont('Arial','B', 10);
            $this->pdf->Cell(9, 0.7, 'TOTAL', 'LB', 0, 'C', 1);
            foreach ($category as $l => $w)
            {
                $this->pdf->Cell($num_col_width, 0.7, number_format($w['total']), 'LBR', 0, 'R', 1); 
            }
            $this->pdf->Ln(0.7);
            $this->pdf->Cell(9, 0.7, 'GRAND TOTAL', 'LB', 0, 'C', 1);
            $this->pdf->Cell(((sizeof($category)-1)*$num_col_width), 0.7, '', 'BR', 0, 'C', 1);
            $this->pdf->Cell($num_col_width, 0.7, number_format($grand_total), 'LBR', 0, 'R', 1);

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
        // $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(8, 1, 'NAMA ITEM / PAKET' , 'LTBR', 0, 'C', 1);

        foreach($this->category as $k => $v)
            $me->Cell(2.5, 1, $v['category_name'] , 'LTBR', 0, 'R', 1);

        $me->Ln(1);
    }
}
?>

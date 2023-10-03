<?php

// Report Fee / Komisi Per Admin
//

class One_wh_005 extends RPT_Controller
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
        $r = $this->r_report->one_wh_005( $this->input->get('item_id'), $this->input->get('sdate'), $this->input->get('edate') );
        // print_r($r);
        //
        $end_stock = 0;

        if ($r)
        {
            $data = isset($r[1])?$r[1]:[];
            $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('P', 'A4');

            $hy = $this->pdf->GetY();
            // $this->pdf->Image(base_url() . '/assets/images/logo-1-1.png', 0.8, 0.7, 3);
            $this->my_header($this, 
                'Kartu Stock', 
                'Item : '.$r['item_name'].' | Periode : ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' - ' .  date('d/m/Y', strtotime($this->input->get('edate'))));
            $end_stock = $r['stock_init'];

            $this->pdf->SetFillColor(255,255,255);
            $this->pdf->SetTextColor(0,0,0);
            $this->pdf->SetFont('Arial','', 9);
            
            $this->pdf->Cell(17, 0.7, "Stok Awal", 'TLB', 0, 'L', 1);
            $this->pdf->Cell(2, 0.7, number_format($r['stock_init']), 'LTRB', 0, 'R', 1);
            $this->pdf->Ln(1);

            $this->tableHeader($this->pdf);

            $this->pdf->SetFillColor(255,255,255);
            $this->pdf->SetTextColor(0,0,0);
            $this->pdf->SetFont('Arial','', 9);
            foreach ($data as $k => $v)
            {
                $this->pdf->Cell(1, 0.7, $k+1, 'LB', 0, 'C', 1);
                $this->pdf->Cell(2, 0.7, date('d-m-Y', strtotime($v['trans_date'])), 'LB', 0, 'C', 1);
                $this->pdf->Cell(10, 0.7, $v['note'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(2, 0.7, $v['item_qty'] > 0 ? number_format($v['item_qty']) : 0, 'LB', 0, 'R', 1);
                $this->pdf->Cell(2, 0.7, $v['item_qty'] < 0 ? number_format($v['item_qty']) : 0, 'LB', 0, 'R', 1);
                $this->pdf->Cell(2, 0.7, number_format($v['after_qty']), 'LBR', 0, 'R', 1);
                $this->pdf->Ln(0.7);

                // $grand_total += $v['F_FeeTotal'];
                $end_stock = $v['after_qty'];
            }

            if (sizeof($data) < 1) 
            {
                $this->pdf->Cell(19, 0.7, 'Tidak Ada Transaksi', 'RTLB', 0, 'C', 1);
                $this->pdf->Ln(0.7);
            }

            $this->pdf->Ln(0.2);
            $this->pdf->Cell(17, 0.7, "Stok Akhir", 'TLB', 0, 'L', 1);
            $this->pdf->Cell(2, 0.7, number_format($end_stock), 'LTRB', 0, 'R', 1);
        //     $this->pdf->SetFillColor(222,222,222);
        //     $this->pdf->SetTextColor(0,0,0);
        //     $this->pdf->Cell(16, 0.7, 'GRAND TOTAL', 'LB', 0, 'C', 1);
        //     $this->pdf->Cell(3, 0.7, number_format($grand_total), 'LBR', 0, 'R', 1);
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
        $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(10, 1, 'KETERANGAN' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'MASUK' , 'LTBR', 0, 'R', 1);
        $me->Cell(2, 1, 'KELUAR' , 'LTBR', 0, 'R', 1);
        $me->Cell(2, 1, 'STOK' , 'LTBR', 0, 'R', 1);
        
        $me->Ln(1);
    }
}
?>

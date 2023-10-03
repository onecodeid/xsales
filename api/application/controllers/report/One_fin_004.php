<?php

// Report Fee / Komisi Per Admin
//

class One_fin_004 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'FIN-004';
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
        $r = $this->r_report->one_fin_004( $this->input->get('sdate'), $this->input->get('edate'), $this->input->get('exp_id') ? $this->input->get('exp_id') : '0' );
        // print_r($r);
        //
        $grand_total = 0;
        $grand_total_exp = 0;
        $grand_total_nominal = 0;

        $total = 0;
        $total_exp = 0;
        $total_nominal = 0;

        $sub_total = 0;
        $exp_id = "0";

        if ($r)
        {
            $data = [];
            if (isset($r[0]))
                $data = $r[0];
            $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('P', 'A4');

            $hy = $this->pdf->GetY();
            // $this->pdf->Image(base_url() . '/assets/images/logo-1-1.png', 0.8, 0.7, 3);
            $this->my_header($this, 
                'Laporan Gagal / Retur COD', 
                'Periode : ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' - ' .  date('d/m/Y', strtotime($this->input->get('edate'))));

            
            // $this->tableHeader($this->pdf);

            
            foreach ($data as $k => $v)
            {
                if ($exp_id != $v['expedition_id'])
                {
                    if ($exp_id != 0)
                    {
                        $this->pdf->SetFillColor(222,222,222);
                        $this->pdf->SetTextColor(0,0,0);
                        $this->pdf->Cell(11.5, 0.7, 'TOTAL', 'LB', 0, 'C', 1);
                        $this->pdf->Cell(2.5, 0.7, number_format($total_nominal), 'LBR', 0, 'R', 1);
                        $this->pdf->Cell(2.5, 0.7, number_format($total_exp), 'LBR', 0, 'R', 1);
                        $this->pdf->Cell(2.5, 0.7, number_format($total), 'LBR', 0, 'R', 1);
                        $this->pdf->Ln(1.2);
                        // $this->pdf->Ln(0.7);
                    }
                        
                    $exp_id = $v['expedition_id'];

                    $total = 0;
                    $total_exp = 0;
                    $total_nominal = 0;

                    $this->pdf->SetFont('Arial','B', 12);
                    $this->pdf->Cell(10, 0.7, $v['expedition_name'], '', 0, 'L', 0);
                    $this->pdf->Image(base_url().'../ui/app/'.$v['expedition_logo'], 17, $this->pdf->GetY() - 0.4);
                    // $this->pdf->Cell(9, 0.7, base_url().'../ui/app/'.$v['expedition_logo'], '', 0, 'L', 0);
                    $this->pdf->Ln(0.7);
                    $this->tableHeader($this->pdf);

                    $this->pdf->SetFillColor(255,255,255);
                    $this->pdf->SetTextColor(0,0,0);
                    $this->pdf->SetFont('Arial','', 9);

                }
                // if ($is_packet != $v['is_packet'] && (!$this->input->get('type') || $this->input->get('type') == 'A'))
                // {
                //     $is_packet = $v['is_packet'];

                //     $this->pdf->SetFillColor(222,222,222);
                //     $this->pdf->SetTextColor(0,0,0);
                //     $this->pdf->Cell(10, 0.7, 'TOTAL', 'LB', 0, 'C', 1);
                //     $this->pdf->Cell(3, 0.7, number_format($total_trx), 'LBR', 0, 'R', 1);
                //     $this->pdf->Cell(3, 0.7, number_format($total_qty), 'LBR', 0, 'R', 1);
                //     $this->pdf->Cell(3, 0.7, number_format($total), 'LBR', 0, 'R', 1);
                //     $this->pdf->Ln(1);
                    
                //     

                //     $this->pdf->SetFillColor(255,255,255);
                //     $this->pdf->SetTextColor(0,0,0);
                //     $this->pdf->SetFont('Arial','', 9);

                //     $total = 0;
                //     $total_qty = 0;
                //     $total_trx = 0;
                // }

                $this->pdf->Cell(1, 0.7, $k+1, 'LB', 0, 'C', 1);
                // $this->pdf->Cell(2, 0.7, date('d-m-Y', strtotime($v['so_date'])), 'LB', 0, 'C', 1);
                $this->pdf->Cell(2.5, 0.7, $v['so_number'], 'LB', 0, 'L', 1);
                // $this->pdf->Cell(5, 0.7, $v['total'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(2, 0.7, $v['so_date'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(6, 0.7, $v['customer_name'].', '.$v['city_name'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(2.5, 0.7, number_format($v['so_subtotl']), 'LBR', 0, 'R', 1);
                $this->pdf->Cell(2.5, 0.7, number_format($v['exp_cost']), 'LBR', 0, 'R', 1);
                $this->pdf->Cell(2.5, 0.7, number_format($v['total']), 'LBR', 0, 'R', 1);
                $this->pdf->Ln(0.7);

                $grand_total += $v['total'];
                $grand_total_exp += $v['exp_cost'];
                $grand_total_nominal += $v['so_subtotl'];

                $total += $v['total'];
                $total_exp += $v['exp_cost'];
                $total_nominal += $v['so_subtotl'];
            }

            $this->pdf->SetFillColor(222,222,222);
            $this->pdf->SetTextColor(0,0,0);
            $this->pdf->Cell(11.5, 0.7, 'TOTAL', 'LB', 0, 'C', 1);
            $this->pdf->Cell(2.5, 0.7, number_format($total_nominal), 'LBR', 0, 'R', 1);
            $this->pdf->Cell(2.5, 0.7, number_format($total_exp), 'LBR', 0, 'R', 1);
            $this->pdf->Cell(2.5, 0.7, number_format($total), 'LBR', 0, 'R', 1);
            $this->pdf->Ln(1);

            $this->pdf->SetFillColor(222,222,222);
            $this->pdf->SetTextColor(0,0,0);
            $this->pdf->Cell(11.5, 0.7, 'GRAND TOTAL', 'TLB', 0, 'C', 1);
            // $this->pdf->Cell(3, 0.7, number_format($grand_total_trx), 'LBRT', 0, 'R', 1);
            // $this->pdf->Cell(3, 0.7, number_format($grand_total_qty), 'LBRT', 0, 'R', 1);
            $this->pdf->Cell(2.5, 0.7, number_format($grand_total_nominal), 'LBRT', 0, 'R', 1);
            $this->pdf->Cell(2.5, 0.7, number_format($grand_total_exp), 'LBRT', 0, 'R', 1);
            $this->pdf->Cell(2.5, 0.7, number_format($grand_total), 'LBRT', 0, 'R', 1);
        }


        $this->pdf->Output();
    }

    function tableHeader($me)
    {
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $this->pdf->SetFont('Arial','', 10);
        $me->Cell(1, 1, 'NO' , 'LTBR', 0, 'C', 1);
        
        $me->Cell(2.5, 1, 'NOMOR' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(6, 1, 'NAMA CUSTOMER' , 'LTBR', 0, 'C', 1);
        $me->Cell(2.5, 1, 'NOMINAL' , 'LTBR', 0, 'R', 1);
        $me->Cell(2.5, 1, 'ONGKIR' , 'LTBR', 0, 'R', 1);
        $me->Cell(2.5, 1, 'DIBAYAR' , 'LTBR', 0, 'R', 1);

        $me->Ln(1);
    }
}
?>

<?php

// Report Fee / Komisi Per Admin
//

class One_sales_008 extends RPT_Controller
{
    var $report_code;
    var $category;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'SALES-008';
    }

    function index() {
        $this->pdf = new PDF("L","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_sales_008( $this->sys_input['staffid'], $this->sys_input['sdate'], $this->sys_input['edate']);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        $this->pdf->SetRptTitle('Rekapitulasi Data Lead per Sales');
        $this->pdf->SetRptSubtitle($r[0][0]['staff_name'] . ' | Periode : '.date('d/m/Y', strtotime($this->sys_input['sdate'])).' s/d '.date('d/m/Y', strtotime($this->sys_input['edate'])));
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        
        
        $grand_total = 0;
        $grand_total_qty = 0;
        $grand_total_trx = 0;

        $total = 0;
        $total_qty = 0;
        $total_trx = 0;

        $sub_total = 0;
        $is_packet = "N";

        $top = true;
        $staff = 0;

        $totals = [];

        if ($r)
        {
            $header = $r[0];
            $data = $r[1];
            // $this->category = $category;
            
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');

            // $hy = $this->pdf->GetY();
            // $this->pdf->Image(base_url() . '/assets/images/logo-1-1.png', 0.8, 0.7, 3);
            // $this->my_header($this, 
            //     'Laporan Omzet Per Produk Per Kategori', 
            //     'Periode : ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' - ' .  date('d/m/Y', strtotime($this->input->get('edate'))),
            //     'L');
            
            // $this->tableHeader($this->pdf, $header);
            $prospect = json_decode($header[0]['prospect']);
            $category = json_decode($header[0]['category']);

            // $this->pdf->SetFillColor(255,255,255);
            // $this->pdf->SetTextColor(0,0,0);
            // $this->pdf->SetFont('Arial','', 9);
            // $num_col_width = 2.5;

            $this->tableHeader($this->pdf, $header);
            foreach ($data as $k => $v)
            {
                if ($this->pdf->GetY()>20) 
                {
                    $this->total($this->pdf, $totals);
                    $this->pdf->AddPage('L', 'A4');
                    $this->tableHeader($this->pdf, $header);
                }
                // if ($v['staff_id'] != $staff)
                // {
                //     if ($k>0)
                //     {
                //         $this->total($this->pdf, $totals);
                //     }

                //     $this->pdf->Ln(0.2);
                    

                //     foreach ($totals as $l => $w)
                //         $totals[$l] = ['b2b'=>0,'b2c'=>0];
                // }
                

                $details = json_decode($v['details']);

                $this->pdf->Cell(2, 0.7, $v['lead_date'], 'LBR', 0, 'C', 0);
                foreach ($prospect as $l => $w)
                    foreach ($details as $ll => $ww)
                        if ($ww->d_type=="P" && $ww->d_pid==$w->h_id)
                        {
                            $this->pdf->Cell(1.2, 0.7, $ww->d_b2b, 'BR', 0, 'C', 1);
                            $this->pdf->Cell(1.2, 0.7, $ww->d_b2c, 'BR', 0, 'C', 0);
                            
                            if (!isset($totals[$ww->d_type.$ww->d_pid]))
                                $totals[$ww->d_type.$ww->d_pid] = ['b2b'=>$ww->d_b2b,'b2c'=>$ww->d_b2c];
                            else
                            {
                                $totals[$ww->d_type.$ww->d_pid]['b2b'] += $ww->d_b2b;
                                $totals[$ww->d_type.$ww->d_pid]['b2c'] += $ww->d_b2c;
                            }
                        }

                $this->pdf->Cell(0.1, 0.7, '' , '', 0, 'C', 0);
                foreach ($category as $l => $w)
                    foreach ($details as $ll => $ww)
                        if ($ww->d_type=="C" && $ww->d_cid==$w->h_id)
                        {
                            $this->pdf->Cell(1.2, 0.7, $ww->d_b2b, 'BR', 0, 'C', 1);
                            $this->pdf->Cell(1.2, 0.7, $ww->d_b2c, 'BR', 0, 'C', 0);

                            if (!isset($totals[$ww->d_type.$ww->d_cid]))
                                $totals[$ww->d_type.$ww->d_cid] = ['b2b'=>$ww->d_b2b,'b2c'=>$ww->d_b2c];
                            else
                            {
                                $totals[$ww->d_type.$ww->d_cid]['b2b'] += $ww->d_b2b;
                                $totals[$ww->d_type.$ww->d_cid]['b2c'] += $ww->d_b2c;
                            }
                        }
                            
            //     foreach ($category as $l => $w)
            //     {
            //         $nominal = $v['category_id'] == $w['category_id'] ? $v['total'] : 0;
            //         $this->pdf->Cell($num_col_width, 0.7, number_format($nominal), 'LBR', 0, 'R', 1);
            //         $category[$l]['total'] += $nominal;
            //     }
            //     $grand_total += $v['total'];
                $staff = 
                $this->pdf->Ln(0.7);
            }

            $this->total($this->pdf, $totals);

            // $this->pdf->SetFont('Arial','B', 10);
            // $this->pdf->Cell(9, 0.7, 'TOTAL', 'LB', 0, 'C', 1);
            // foreach ($category as $l => $w)
            // {
            //     $this->pdf->Cell($num_col_width, 0.7, number_format($w['total']), 'LBR', 0, 'R', 1); 
            // }
            // $this->pdf->Ln(0.7);
            // $this->pdf->Cell(9, 0.7, 'GRAND TOTAL', 'LB', 0, 'C', 1);
            // $this->pdf->Cell(((sizeof($category)-1)*$num_col_width), 0.7, '', 'BR', 0, 'C', 1);
            // $this->pdf->Cell($num_col_width, 0.7, number_format($grand_total), 'LBR', 0, 'R', 1);
        }

        $this->pdf->Output();
    }

    function tableHeader($me, $data)
    {
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(0,0,0);
        $this->pdf->SetFont('Arial','B', 9);
        $me->SetTextColor(255,255,255);

        $me->Cell(2, 1.4, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        
        $prospect = json_decode($data[0]['prospect']);
        $category = json_decode($data[0]['category']);

        // $me->Cell(4, 1, 'NAMA ITEM / PAKET' , 'LTBR', 0, 'C', 1);
        foreach ($prospect as $k => $v)
            $me->Cell(2.4, 0.7, $v->h_name , 'TBR', 0, 'C', 1);

        $me->Cell(0.1, 0.7, '' , '', 0, 'C', 0);
        $me->SetFillColor(33,33,33);
        foreach ($category as $k => $v)
            $me->Cell(2.4, 0.7, $v->h_name , 'TBR', 0, 'C', 1);
        $me->Ln(0.7);
        
        $me->Cell(2, 0.7, '' , '', 0, 'C', 0);
        for ($i=0; $i<(sizeof($prospect)+sizeof($category));$i++)
        {
            if ($i<sizeof($prospect)) $me->SetFillColor(0,0,0);
            else $me->SetFillColor(33,33,33);
            if ($i==sizeof($prospect)) $me->Cell(0.1, 0.7, '' , '', 0, 'C', 0);
            $me->Cell(1.2, 0.7, 'B2B' , 'LBR', 0, 'C', 1);
            $me->Cell(1.2, 0.7, 'P' , 'LBR', 0, 'C', 1);
        }
            
        $me->Ln(0.7);
        $me->SetFillColor(192,192,192);
        $me->SetTextColor(0,0,0);
        $this->pdf->SetFont('Arial','', 9);
    }

    function total($me, $data)
    {
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $div = 3;

        $me->Cell(2, 0.7, 'TOTAL' , 'LTBR', 0, 'C', 1);
        foreach ($data as $k => $v)
        {         
            $me->Cell(1.2, 0.7, $v['b2b'] , 'LTBR', 0, 'C', 1);
            $me->Cell(1.2, 0.7, $v['b2c'] , 'LTBR', 0, 'C', 1);
        }

        $me->Ln(0.7);
        $me->SetFillColor(192,192,192);
        $me->SetTextColor(0,0,0);
        $this->pdf->SetFont('Arial','', 9);
    }
}
?>

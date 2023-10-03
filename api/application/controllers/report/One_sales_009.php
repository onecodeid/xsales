<?php

// Report Fee / Komisi Per Admin
//

class One_sales_009 extends RPT_Controller
{
    var $report_code;
    var $category;
    var $limit_per_section;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'SALES-009';
        $this->limit_per_section = 8;
    }

    function index() {
        $this->pdf = new PDF("L","cm",array(21,29.6));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        $this->pdf->SetRptTitle('Rekapitulasi Data Lead per Sales');
        $this->pdf->SetRptSubtitle('Periode : '.date('d/m/Y', strtotime($this->sys_input['sdate'])).' s/d '.date('d/m/Y', strtotime($this->sys_input['edate'])));
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_sales_009( $this->sys_input['sdate'], $this->sys_input['edate'] );
        
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
        $totals2 = [];

        if ($r)
        {
            $header = $r[0];
            $data = $r[1];
            
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');

            $prospect = json_decode($header[0]['prospect']);
            $category = json_decode($header[0]['category']);

            $this->tableHeader($this->pdf, $header);
            foreach ($data as $k => $v)
            {
                $details = json_decode($v['details']);
                $this->pdf->Cell(5, 0.7, $v['staff_name'], 'LBR', 0, 'L', 0);
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
                
                $this->pdf->Ln(0.7);
            }

            $this->total($this->pdf, $totals);

            $this->pdf->Ln(0.7);
            

            $nctg = [[],[]];
            foreach ($category as $k => $v)
            {
                if ($k < $this->limit_per_section)
                    $nctg[0][] = $v;
                else $nctg[1][] = $v;
            }

            foreach ($nctg as $m => $n)
            { 
                $this->tableHeader2($this->pdf, $header, $m);
                foreach ($data as $k => $v)
                {
                    $details = json_decode($v['details']);
                    $this->pdf->Cell(5, 0.7, $v['staff_name'], 'LBR', 0, 'L', 0);
                    
                    foreach ($n as $l => $w)
                    {
                        foreach ($details as $ll => $ww)
                            if ($ww->d_type=="C" && $ww->d_cid==$w->h_id)
                            {
                                $this->pdf->Cell(1.2, 0.7, $ww->d_b2b, 'BR', 0, 'C', 1);
                                $this->pdf->Cell(1.2, 0.7, $ww->d_b2c, 'BR', 0, 'C', 0);

                                if (!isset($totals2[$ww->d_type.$ww->d_cid]))
                                    $totals2[$ww->d_type.$ww->d_cid] = ['b2b'=>$ww->d_b2b,'b2c'=>$ww->d_b2c];
                                else
                                {
                                    $totals2[$ww->d_type.$ww->d_cid]['b2b'] += $ww->d_b2b;
                                    $totals2[$ww->d_type.$ww->d_cid]['b2c'] += $ww->d_b2c;
                                }
                            }
                    }
                        
                    $this->pdf->Ln(0.7);
                }
                $this->total($this->pdf, $totals2, $m);
                
                $this->pdf->AddPage('L', 'A4');
            }
            

            
        }

        $this->pdf->Output();
    }

    function tableHeader($me, $data)
    {
        $me->SetFillColor(0,0,0);
        $this->pdf->SetFont('Arial','B', 9);
        $me->SetTextColor(0,0,0);
        $me->Cell(5, 0.7, 'BERDASARKAN PROSPEK' , '', 0, 'L', 0);
        $me->Ln(0.7);
        $me->SetTextColor(255,255,255);
        $me->Cell(5, 1.4, 'SALES' , 'LTBR', 0, 'C', 1);
        $prospect = json_decode($data[0]['prospect']);

        foreach ($prospect as $k => $v)
            $me->Cell(2.4, 0.7, $v->h_name , 'TBR', 0, 'C', 1);
        $me->Ln(0.7);
        
        $me->Cell(5, 0.7, '' , '', 0, 'C', 0);
        for ($i=0; $i<(sizeof($prospect));$i++)
        {
            $me->Cell(1.2, 0.7, 'B2B' , 'LBR', 0, 'C', 1);
            $me->Cell(1.2, 0.7, 'P' , 'LBR', 0, 'C', 1);
        }
            
        $me->Ln(0.7);
        $me->SetFillColor(192,192,192);
        $me->SetTextColor(0,0,0);
        $this->pdf->SetFont('Arial','', 9);
    }

    function tableHeader2($me, $data, $n = 0)
    {
        $xn = $this->limit_per_section * $n;

        $me->SetFillColor(0,0,0);
        $this->pdf->SetFont('Arial','B', 9);
        $me->SetTextColor(0,0,0);
        $me->Cell(5, 0.7, 'BERDASARKAN KATEGORI' , '', 0, 'L', 0);
        $me->Ln(0.7);
        $me->SetTextColor(255,255,255);
        $me->Cell(5, 1.4, 'SALES' , 'LTBR', 0, 'C', 1);
        $category = json_decode($data[0]['category']);

        // patch
        $ctgs = [];
        foreach ($category as $k => $v)
            if ($k >= $xn && $k < $xn + $this->limit_per_section)
                $ctgs[] = $v;
        $category = $ctgs;
        // end of patch

        foreach ($category as $k => $v)
            $me->Cell(2.4, 0.7, $v->h_name , 'TBR', 0, 'C', 1);
        $me->Ln(0.7);
        
        $me->Cell(5, 0.7, '' , '', 0, 'C', 0);
        
        for ($i=0; $i<(sizeof($category));$i++)
        {
            $me->Cell(1.2, 0.7, 'B2B' , 'LBR', 0, 'C', 1);
            $me->Cell(1.2, 0.7, 'P' , 'LBR', 0, 'C', 1);
        }
            
        $me->Ln(0.7);
        $me->SetFillColor(192,192,192);
        $me->SetTextColor(0,0,0);
        $this->pdf->SetFont('Arial','', 9);
    }

    function total($me, $data, $n = 0)
    {
        $xn = $this->limit_per_section * $n;

        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $div = 3;

        $me->Cell(5, 0.7, 'TOTAL' , 'LTBR', 0, 'C', 1);
        $i = 0;
        foreach ($data as $k => $v)
        {         
            if ($i >= $xn && $i < ($this->limit_per_section + $xn))
            {
                $me->Cell(1.2, 0.7, $v['b2b'] , 'LTBR', 0, 'C', 1);
                $me->Cell(1.2, 0.7, $v['b2c'] , 'LTBR', 0, 'C', 1);
            }
            $i++;
        }

        $me->Ln(0.7);
        $me->SetFillColor(192,192,192);
        $me->SetTextColor(0,0,0);
        $this->pdf->SetFont('Arial','', 9);
    }
}
?>

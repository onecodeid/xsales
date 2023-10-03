<?php

// Report Fee / Komisi Per Admin
//

class One_master_001 extends RPT_Controller
{
    var $report_code;
    // var $pdf;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'MASTER-001';
    }

    function index() {
        $this->pdf = new FPDF("L", "cm");
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        // $this->pdf->header_func = "my_header";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        
        if (!isset($this->sys_input['uid']))
            $uid = $this->sys_user['user_id'];
        else
            $uid = $this->sys_input['uid'];

        $provinceid = 0;
        $cityid = 0;
        $levelid = 0;
        if (isset($this->sys_input['city_id'])) $cityid = $this->sys_input['city_id'];
        if (isset($this->sys_input['province_id'])) $provinceid = $this->sys_input['province_id'];
        if (isset($this->sys_input['level_id'])) $levelid = $this->sys_input['level_id'];

        $r = $this->r_report->one_master_001( $uid, $provinceid, $cityid, $levelid );
        //
        $grand_total = 0;
        $sub_total = 0;
        $curr_province = 0;

        if ($r)
        {
            $data = $r[1];
            $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');

            $hy = $this->pdf->GetY();
            // $this->pdf->Image(base_url() . '/assets/images/logo-1-1.png', 0.8, 0.7, 3);
            $this->my_header($this, 
                'Laporan Data Customer', 
                'Admin : '.$r['S_UserUsername'], 'L');

            
            

            
            foreach ($data as $k => $v)
            {
                if ($v['province_id'] != $curr_province)
                {
                    $curr_province = $v['province_id'];
                    $this->tableHeader($this->pdf, $v['province_name']);
                    
                    $this->pdf->SetFillColor(255,255,255);
                    $this->pdf->SetTextColor(0,0,0);
                    $this->pdf->SetFont('Arial','', 9);
                }

                
                $this->pdf->Cell(1, 0.7, $k+1, 'LB', 0, 'C', 1);
                $this->pdf->Cell(4, 0.7, $v['customer_name'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(13, 0.7, $v['customer_address'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(4, 0.7, strtoupper($v['city_name']), 'LB', 0, 'L', 1);
                $this->pdf->Cell(3, 0.7, $v['customer_phone'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(3, 0.7, strtoupper($v['level_name']), 'LBR', 0, 'L', 1);
                $this->pdf->Ln(0.7);
            }

            // $this->pdf->SetFillColor(222,222,222);
            // $this->pdf->SetTextColor(0,0,0);
            // $this->pdf->Cell(16, 0.7, 'GRAND TOTAL', 'LB', 0, 'C', 1);
            // $this->pdf->Cell(3, 0.7, number_format($grand_total), 'LBR', 0, 'R', 1);
        }


        $this->pdf->Output();
    }

    function tableHeader($me, $province = '')
    {
        $this->pdf->SetFont('Arial','', 10);
        $me->SetFillColor(255, 255, 255);
        $me->SetTextColor(0, 0, 0);
        $me->Cell(28, 1, strtoupper($province) , '', 0, 'L', 1);
        $me->Ln(0.8);

        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->Cell(1, 1, 'NO' , 'LTBR', 0, 'C', 1);
        $me->Cell(4, 1, 'NAMA CUSTOMER' , 'LTBR', 0, 'C', 1);
        $me->Cell(13, 1, 'ALAMAT' , 'LTBR', 0, 'C', 1);
        $me->Cell(4, 1, 'KOTA' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'TELEPON' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'LEVEL' , 'LTBR', 0, 'C', 1);

        $me->Ln(1);
    }
}
?>

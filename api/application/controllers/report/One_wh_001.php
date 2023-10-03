<?php

class One_wh_001 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'IV-001';
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
        $r = $this->r_report->one_wh_001( $this->input->get('soid') );

        //
        // $grand_total = 0;
        // $sub_total = 0;

        if ($r)
        {
            $data = $r[1];
            $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('P', 'A4');

            $hy = $this->pdf->GetY();
            $this->pdf->Image(base_url() . '/assets/images/logo-1-1.png', 0.8, 0.7, 3);

            $this->pdf->SetY($hy);
            $this->pdf->SetFont('Arial','B', 10);
            $this->pdf->Cell(19, 0.7, 'No Order : ' . $r['order_number'], '', 0, 'R', 0);
            $this->pdf->Ln(0.5);
            $this->pdf->SetFont('Arial','', 10);
            $this->pdf->Cell(19, 0.7, 'Tanggal Invoice : ' . date('d-m-Y', strtotime($r['invoice_date'])), '', 0, 'R', 0);
            // $this->pdf->Ln(0.5);
            // $this->pdf->Cell(19, 0.7, 'Metode Pengiriman : ' . $r['expedition_name'] . ', ' . $r['service_name'], '', 0, 'R', 0);
            $this->pdf->SetY($hy + 1.6);
            $this->pdf->SetFont('Arial','B', 10);
            $this->pdf->Cell(9, 0.7, 'Pengirim : ', '', 0, 'L', 0);
            $this->pdf->Ln(0.5);
            $this->pdf->SetFont('Arial','', 10);
            $this->pdf->Cell(9, 0.7, '' . $r['sender_name'], '', 0, 'L', 0);
            $this->pdf->Ln(0.6);
            $this->pdf->MultiCell(9, 0.5, $r['sender_address'], '', 'L', 0);
            $this->pdf->Cell(9, 0.7, 'Kec. ' . $r['sender_district'] . ', ' . $r['sender_city'], '', 0, 'L', 0);
            $this->pdf->Ln(0.5);
            $this->pdf->Cell(9, 0.7, '' . $r['sender_province'] . '  -  ' . $r['sender_phone'], '', 0, 'L', 0);

            $this->pdf->SetY($hy + 1.6);
            $this->pdf->Cell(10, 0.7, '', '', 0, 'L', 0);
            $this->pdf->SetFont('Arial','B', 10);
            $this->pdf->Cell(9, 0.7, 'Alamat Penerima : ', '', 0, 'L', 0);
            $this->pdf->Ln(0.5);
            $this->pdf->SetFont('Arial','', 10);
            $this->pdf->Cell(10, 0.7, '', '', 0, 'L', 0);
            $this->pdf->Cell(9, 0.7, '' . $r['sh_customer_name'], '', 0, 'L', 0);
            $this->pdf->Ln(0.6);
            $this->pdf->Cell(10, 0.7, '', '', 0, 'L', 0);
            $this->pdf->MultiCell(9, 0.5, $r['sh_customer_address'], '', 'L', 0);
            $this->pdf->Cell(10, 0.7, '', '', 0, 'L', 0);
            $this->pdf->Cell(9, 0.7, 'Kec. ' . $r['sh_customer_district'] . ', ' . $r['sh_customer_city'], '', 0, 'L', 0);
            $this->pdf->Ln(0.5);
            $this->pdf->Cell(10, 0.7, '', '', 0, 'L', 0);
            $this->pdf->Cell(9, 0.7, '' . $r['sh_customer_province'] . '  -  ' . $r['sh_customer_phone'], '', 0, 'L', 0);
            
            $this->pdf->Ln(0.7);
            $this->pdf->SetFont('Arial','B', 10);
            $this->pdf->Cell(10, 0.7, '', '', 0, 'L', 0);
            $this->pdf->Cell(9, 0.7, 'Metode Pengiriman :', '', 0, 'L', 0);

            $this->pdf->Ln(0.5);
            $this->pdf->SetFont('Arial','', 10);
            $this->pdf->Cell(10, 0.7, '', '', 0, 'L', 0);

            if ($r['expedition_code'] == 'EX.OTHER') {
                $this->pdf->Cell(9, 0.7, $r['expedition_name'], '', 0, 'L', 0);
                $this->pdf->Ln(0.5);
                $this->pdf->Cell(10, 0.7, '', '', 0, 'L', 0);
                $this->pdf->Cell(9, 0.7, $r['exp_other'], '', 0, 'L', 0);
            } else if ($r['expedition_code'] == 'EX.FREE') {
                $this->pdf->Cell(9, 0.7, $r['expedition_name'], '', 0, 'L', 0);
                $this->pdf->Ln(0.5);
                $this->pdf->Cell(10, 0.7, '', '', 0, 'L', 0);
                $this->pdf->Cell(9, 0.7, $r['exp_note'], '', 0, 'L', 0);
            } else {
                $this->pdf->Cell(9, 0.7, $r['expedition_name'] . ', ' . $r['service_name'], '', 0, 'L', 0);
            }

            $this->pdf->Ln(1.2);
            $this->tableHeader($this->pdf);

            $n = 0;
            $this->pdf->SetTextColor(0,0,0);
            foreach ($data as $k => $v)
            {
                $n = $k;
                if ($k % 2 == 0)
                    $this->pdf->SetFillColor(255,255,255);
                else
                    $this->pdf->SetFillColor(220,220,220);
                $this->pdf->Cell(16, 1, '  '.$v['item_name'], 'LB', 0, 'L', 1);
                $this->pdf->Cell(3, 1, $v['item_app_qty'], 'LBR', 0, 'C', 1);
                $this->pdf->Ln(1);
            }
            $n++;

        }

        $this->pdf->Output();
    }

  function myFooter($me) {
    $me->pdf->SetFont("ArialNarrow","",8);
    $me->pdf->SetXY(1,-1);
    $me->pdf->MultiCell(19,1,"LKK/MCU/2015/" ,"","C");
    $me->pdf->SetXY(1,-1);
    $me->pdf->MultiCell(19,1,"Halaman : " . $me->pdf->PageNo() ,"","R");
  }

    function tableHeader($me)
    {
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->Cell(16, 1, 'NAMA ITEM' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'QTY' , 'LTBR', 0, 'C', 1);

        // $me->Cell(4, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
        $me->Ln(1);
    }
}
?>

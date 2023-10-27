<?php

// Report All Stock All Warehouse

class One_sales_022 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'SAL-022';
        $this->wQty = 2;
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Laporan Penjualan');
        $this->pdf->setRptSubtitle('Periode '.date('d M Y', strtotime($this->sys_input['sdate'])).' s/d '.date('d M Y', strtotime($this->sys_input['edate'])).' ');
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_sales_022( $this->sys_input['sdate'], $this->sys_input['edate'], (isset($this->sys_input['customer'])?$this->sys_input['customer']:0) );

        //
        $this->load->model('system/s_staff');
        // $staff = $this->s_staff->get( $this->sys_input['staffid'] );
        // print_r($staff);
        $grand_total = 0;
        $disc_total = 0;
        $bruto_total = 0;
        $sub_total = 0;
        $sub_total_ppn = 0;
        $staff_id = 0;

        
        if ($r)
        {
            // $d = [];
            // $items = [];
            // foreach ($r[0] as $k => $v)
            // {
            //     $items['I'.$v['item_id']] = $v;
            //     if (!isset($d['I'.$v['item_id']]))
            //         $d['I'.$v['item_id']] = [];
            //     $d['I'.$v['item_id']][$v['warehouse_code']] = ['qty'=>$v['stock_qty'],'hpp'=>$v['item_hpp']];
            // }

            $d = $r;
            
            if (sizeof($d) > 0)
            {
                $staff = [];
                $staff_id = 0;
            }
            
            // $data = isset($r[1])?$r[1]:[];
            // $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('P', 'A4');
            $width = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin;


            $this->tableHeader($this->pdf, ['staff_name' => '', 'sub_total' => $sub_total, 'bruto_total' => $bruto_total, 'disc_total' => $disc_total]);

            $this->pdf->SetFont('Arial','', 9);

            $wQty = $this->wQty;
            $wItemName = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin;

            foreach ($d as $k => $v)
            {
                // if ($staff_id != $v['staff_id'])
                // {
                //     $staff_id = 0;//$v['staff_id'];
                //     $staff = []; //$this->s_staff->get( $staff_id );

                //     $this->tableFooter($this->pdf, ['sub_total' => $sub_total, 'bruto_total' => $bruto_total, 'disc_total' => $disc_total]);
                //     $sub_total = 0;
                //     $sub_total_ppn = 0;

                //     $this->tableHeader($this->pdf, ['staff_name' => '', 'sub_total' => $sub_total, 'bruto_total' => $bruto_total, 'disc_total' => $disc_total]);
                // }

                $ylimit = $this->pdf->h - 2.9;
                if ($this->pdf->GetY() > $ylimit)
                {
                    $this->tableFooter($this->pdf, ['sub_total' => $sub_total, 'bruto_total' => $bruto_total, 'disc_total' => $disc_total]);
                    $this->pdf->AddPage('P', 'A4');
                    $this->tableHeader($this->pdf, ['staff_name' => '', 'sub_total' => $sub_total, 'bruto_total' => $bruto_total, 'disc_total' => $disc_total]);
                }

                $this->pdf->Cell(1, 0.7, $k+1, 'LBR', 0, 'C', 0);
                $this->pdf->Cell(2, 0.7, $v['invoice_date'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(2.5, 0.7, $v['invoice_number'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell($width-8.5, 0.7, $v['customer_name'], 'LBR', 0, 'L', 0);
                // $this->pdf->Cell(3, 0.7, number_format($v['item_bruto']), 'BR', 0, 'R', 0);
                // $this->pdf->Cell(2.5, 0.7, number_format($v['item_disc'] + $v['item_disctotal']), 'BR', 0, 'R', 0);
                $this->pdf->Cell(3, 0.7, number_format($v['item_subtotal'] + ($v['item_incppn']=="Y"?0:$v['item_ppnamount'])), 'BR', 0, 'R', 0);
                

                $this->pdf->Ln(0.7);

                $sub_total += $v['item_subtotal'] + ($v['item_incppn']=="Y"?0:$v['item_ppnamount']);
                $sub_total_ppn += $v['item_ppnamount'];
                $disc_total += $v['item_disc'];
                $bruto_total += $v['item_bruto'];
            }

            $this->tableFooter($this->pdf, ['sub_total' => $sub_total, 'bruto_total' => $bruto_total, 'disc_total' => $disc_total]);
            // $this->pdf->Ln(0.2);
            // $this->pdf->SetFont('Arial','B', 9);
            // $this->pdf->Cell($wItemName+3, 0.7, 'TOTAL', 'BLTR', 0, 'C', 0);
            // $this->pdf->Cell($wQty, 0.7, '', 'BTR', 0, 'R', 0);
            // $this->pdf->Cell(2, 0.7, '', 'BTR', 0, 'C', 0);
            // $this->pdf->Cell($wQty, 0.7, '', 'BTR', 0, 'R', 0);
            // $this->pdf->Cell($wQty+1, 0.7, number_format($grand_total, 2), 'BTR', 0, 'R', 0);

           
            
        }

       
        $this->pdf->Output();
    }

    function tableHeader($me, $d)
    {
        $wItemName = $me->w - $me->lMargin - $me->rMargin - 9;
        $this->pdf->SetFont('Arial','', 9);
        
        if ($me->PageNo()>1) {
            $me->SetFillColor(0,0,0);
            $me->SetTextColor(0,0,0);
            $me->Cell($me->w - $me->lMargin - $me->rMargin - 8, 1, "", '', 0, 'L', 0);
            $me->Cell(5, 1, "SubTotal : " , '', 0, 'R', 0);
            // $me->Cell(3, 0.7, number_format($d['bruto_total']) , 'LBR', 0, 'R', 0);
            // $me->Cell(2.5, 0.7, number_format($d['disc_total']) , 'LBR', 0, 'R', 0);
            $me->Cell(3, 0.7, number_format($d['sub_total']) , '', 0, 'R', 0);
            $me->Ln(0.8);
        }
        

        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->Cell(1, 1, 'NO' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'NOMOR' , 'LTBR', 0, 'C', 1);
        $me->Cell($wItemName, 1, 'NAMA CUSTOMER' , 'LTBR', 0, 'C', 1);
        // $me->Cell(3, 1, 'BRUTO' , 'LTBR', 0, 'C', 1);
        // $me->Cell(2, 1, 'POTONGAN' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'NETTO' , 'LTBR', 0, 'C', 1);

        // $me->Cell(4, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
        $me->Ln(1);
    }

    function tableFooter($me, $d)
    {
        $wItemName = $me->w - $me->lMargin - $me->rMargin - 18;
        $this->pdf->SetFont('Arial','', 9);
        
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(0,0,0);
        $me->Cell($me->w - $me->lMargin - $me->rMargin - 6, 1, "" , '', 0, 'L', 0);
        $me->Cell(3, 0.7, "TOTAL" , 'LBR', 0, 'C', 0);
        // $me->Cell(3, 0.7, number_format($d['bruto_total']) , 'LBR', 0, 'R', 0);
        // $me->Cell(2.5, 0.7, number_format($d['disc_total']) , 'LBR', 0, 'R', 0);
        $me->Cell(3, 0.7, number_format($d['sub_total']) , 'BR', 0, 'R', 0);
        // $me->Ln(1);

        // $me->SetFillColor(0,0,0);
        // $me->SetTextColor(255,255,255);
        // $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        // $me->Cell(4, 1, 'NOMOR SO' , 'LTBR', 0, 'C', 1);
        // $me->Cell($wItemName, 1, 'NAMA CUSTOMER' , 'LTBR', 0, 'C', 1);
        // $me->Cell(5, 1, 'CATATAN' , 'LTBR', 0, 'C', 1);
        // $me->Cell(3, 1, 'NOMINAL' , 'LTBR', 0, 'R', 1);

        // $me->Cell(4, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
        $me->Ln(1);
    }
}
?>

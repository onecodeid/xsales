<?php

// Report All Stock All Warehouse

class One_sales_014 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    var $gsub_total = 0;
    var $gsub_total_ppn = 0;
    var $gsub_total_qty = 0;
    var $gunit = "";
    var $gitem = "";

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'SAL-013';
        $this->wQty = 2;
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Laporan Penjualan Detail Per Produk');
        $this->pdf->setRptSubtitle('Periode '.date('d M Y', strtotime($this->sys_input['sdate'])).' s/d '.date('d M Y', strtotime($this->sys_input['edate'])).' ');
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_sales_014( $this->sys_input['itemid'], $this->sys_input['sdate'], $this->sys_input['edate'] );

        //
        $this->load->model('system/s_staff');
        // $staff = $this->s_staff->get( $this->sys_input['staffid'] );
        // print_r($staff);
        $grand_total = 0;
        $sub_total = 0;
        $sub_total_ppn = 0;
        $sub_total_qty = 0;
        $item_id = 0;

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
                $staff = $this->s_staff->get( $d[0]['staff_id'] );
                $item_id = $d[0]['item_id'];

                $this->gunit = $d[0]['unit_name'];
                $this->gitem = $d[0]['item_name'];
            }
            
            // $data = isset($r[1])?$r[1]:[];
            // $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');
            $this->tableHeader($this->pdf, []);

            $this->pdf->SetFont('Arial','', 9);

            $wQty = $this->wQty;
            $wItemName = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin - 24;

            foreach ($d as $k => $v)
            {
                if ($item_id != $v['item_id'])
                {
                    $item_id = $v['item_id'];
                    $staff = $this->s_staff->get( $v['staff_id'] );

                    $this->tableFooter($this->pdf, []);
                    $sub_total = 0;
                    $sub_total_ppn = 0;
                    $sub_total_qty = 0;

                    $this->gsub_total = 0;
                    $this->gsub_total_ppn = 0;
                    $this->gsub_total_qty = 0;

                    $this->tableHeader($this->pdf, []);
                }

                $ylimit = $this->pdf->h - 2.9;
                if ($this->pdf->GetY() > $ylimit)
                {
                    $this->tableFooter($this->pdf, []);
                    $this->pdf->AddPage('L', 'A4');
                    $this->tableHeader($this->pdf, []);

                    // $sub_total = 0;
                    // $sub_total_ppn = 0;
                }

                $this->pdf->Cell(2, 0.7, $v['invoice_date'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(4, 0.7, $v['invoice_number'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(7, 0.7, $v['customer_name'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell($wItemName, 0.7, $v['staff_name'], 'BR', 0, 'L', 0);
                $this->pdf->Cell(2, 0.7, number_format($v['item_price']), 'BR', 0, 'R', 0);
                $this->pdf->Cell(2, 0.7, number_format($v['item_qty']) . ' ' . $v['unit_name'], 'BR', 0, 'R', 0);
                $this->pdf->Cell(2, 0.7, number_format($v['item_disc'] + $v['item_disctotal']), 'BR', 0, 'R', 0);
                $this->pdf->Cell(2, 0.7, number_format($v['item_ppnamount']), 'BR', 0, 'R', 0);
                $this->pdf->Cell(3, 0.7, number_format($v['item_subtotal'] + ($v['item_incppn']=="Y"?0:$v['item_ppnamount'])), 'BR', 0, 'R', 0);
                

                $this->pdf->Ln(0.7);

                $sub_total += $v['item_subtotal'] + ($v['item_incppn']=="Y"?0:$v['item_ppnamount']);
                $sub_total_ppn += $v['item_ppnamount'];
                $sub_total_qty += $v['item_qty'];

                $this->gsub_total += $v['item_subtotal'] + ($v['item_incppn']=="Y"?0:$v['item_ppnamount']);
                $this->gsub_total_ppn += $v['item_ppnamount'];
                $this->gsub_total_qty += $v['item_qty'];
            }

            $this->tableFooter($this->pdf, []);
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
        $wItemName = $me->w - $me->lMargin - $me->rMargin - 24;
        $this->pdf->SetFont('Arial','', 9);
        
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(0,0,0);
        $me->Cell($me->w - $me->lMargin - $me->rMargin - 11, 1, "PRODUK : " . strtoupper($this->gitem) , '', 0, 'L', 0);
        $me->Cell(5, 1, "SubTotal : " , '', 0, 'R', 0);
        $me->Cell(3, 0.7, number_format($this->gsub_total_ppn) , '', 0, 'R', 0);
        $me->Cell(3, 0.7, number_format($this->gsub_total) , '', 0, 'R', 0);
        $me->Ln(0.8);

        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(4, 1, 'NOMOR SO' , 'LTBR', 0, 'C', 1);
        $me->Cell(7, 1, 'NAMA CUSTOMER' , 'LTBR', 0, 'C', 1);
        $me->Cell($wItemName, 1, 'NAMA SALES' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'HARGA' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'QTY' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'DISC' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'PPN' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'SUBTOTAL' , 'LTBR', 0, 'C', 1);

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
        $me->Cell($me->w - $me->lMargin - $me->rMargin - 13, 1, "" , '', 0, 'L', 0);
        $me->Cell(3, 0.7, "TOTAL" , 'LBR', 0, 'C', 0);
        $me->Cell(3, 0.7, number_format($this->gsub_total_qty) . " " . $this->gunit, 'LBR', 0, 'R', 0);
        $me->Cell(4, 0.7, number_format($this->gsub_total_ppn) , 'LBR', 0, 'R', 0);
        $me->Cell(3, 0.7, number_format($this->gsub_total) , 'BR', 0, 'R', 0);
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

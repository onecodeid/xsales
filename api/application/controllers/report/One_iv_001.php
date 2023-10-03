<?php

// Report All Stock All Warehouse

class One_iv_001 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'IV-001';
        $this->wQty = 2.5;
    }

    function index() {
        $this->pdf = new PDF("L","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Rekapitulasi Stok');
        $this->pdf->setRptSubtitle('Semua Item / Produk di Semua Gudang');
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_iv_001(  );

        //
        // $grand_total = 0;
        // $sub_total = 0;

        if ($r)
        {
            $d = [];
            $items = [];
            foreach ($r[0] as $k => $v)
            {
                $items['I'.$v['item_id']] = $v;
                if (!isset($d['I'.$v['item_id']]))
                    $d['I'.$v['item_id']] = [];
                $d['I'.$v['item_id']][$v['warehouse_code']] = $v['stock_qty'];
            }

            
            // $data = isset($r[1])?$r[1]:[];
            // $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');

            $this->tableHeader($this->pdf, $r[1]);

            $this->pdf->SetFont('Arial','', 9);

            $wQty = $this->wQty;
            $wItemName = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin - 6 - ($wQty*(sizeof($r[1])+1));

            foreach ($d as $k => $v)
            {
                $ylimit = $this->pdf->h - 2.2;
                if ($this->pdf->GetY() > $ylimit)
                {
                    $this->pdf->AddPage('L', 'A4');
                    $this->tableHeader($this->pdf, $r[1]);
                }

                $this->pdf->Cell(4, 0.7, $items[$k]['item_code'], 'BR', 0, 'L', 0);
                $this->pdf->Cell($wItemName, 0.7, $items[$k]['item_name'], 'BR', 0, 'L', 0);

                $itotal = 0;
                foreach ($r[1] as $l => $w)
                {
                    if (isset($v[$w['warehouse_code']]))
                        $this->pdf->Cell($wQty, 0.7, number_format($v[$w['warehouse_code']], 2), 'BR', 0, 'R', 0);
                    else
                        $this->pdf->Cell($wQty, 0.7, 0, 'BR', 0, 'R', 0);
                    $itotal += $v[$w['warehouse_code']];
                }
                
                // TOTAL COLUMN
                $this->pdf->Cell($wQty, 0.7, number_format($itotal, 2), 'BR', 0, 'R', 0);

                $this->pdf->Cell(2, 0.7, $items[$k]['unit_name'], 'B', 0, 'C', 0);
                $this->pdf->Ln(0.7);
            }
            // $hy = $this->pdf->GetY();
            // $this->pdf->Image(base_url() . '/assets/images/logo-1-1.png', 0.8, 0.7, 3);

            // $this->pdf->SetY(2);
            // $this->pdf->SetFont('Arial','B', 10);
            // $this->pdf->Cell(6, 0.7, 'No Order : ' . $r['order_number'], '', 0, 'L', 0);
            // $this->pdf->Ln(0.5);
            // $this->pdf->SetFont('Arial','', 10);
            // $this->pdf->Cell(6, 0.7, 'Tanggal Invoice : ' . date('d-m-Y', strtotime($r['invoice_date'])), '', 0, 'L', 0);
            // $this->pdf->Ln(0.5);
            // $this->pdf->Cell(6, 0.7, 'Metode Pengiriman : ', '', 0, 'L', 0);
            // $this->pdf->Ln(0.5);

            // if ($r['expedition_code'] == 'EX.OTHER') {
            //     $this->pdf->Cell(6, 0.7, '---  ' . $r['expedition_name'], '', 0, 'L', 0);
            //     $this->pdf->Ln(0.5);
            //     $this->pdf->Cell(6, 0.7, '      ' . $r['exp_other'], '', 0, 'L', 0);
            // } else if ($r['expedition_code'] == 'EX.FREE') {
            //     $this->pdf->Cell(6, 0.7, '---  ' . $r['expedition_name'], '', 0, 'L', 0);
            //     $this->pdf->Ln(0.5);
            //     $this->pdf->Cell(6, 0.7, '      ' . $r['exp_note'], '', 0, 'L', 0);
            // } else {
            //     $this->pdf->Cell(6, 0.7, '---  ' . $r['expedition_name'] . ', ' . $r['service_name'], '', 0, 'L', 0);
            // }
            

            // $this->pdf->SetY($hy);
            // $this->pdf->Cell(6.5, 0.7, '', '', 0, 'L', 0);
            // $this->pdf->SetFont('Arial','B', 10);
            // $this->pdf->Cell(6, 0.7, 'Alamat Penagihan : ', '', 0, 'L', 0);
            // $this->pdf->Ln(0.5);
            // $this->pdf->SetFont('Arial','', 10);
            // $this->pdf->Cell(6.5, 0.7, '', '', 0, 'L', 0);
            // $this->pdf->Cell(6, 0.7, '' . $r['customer_name'], '', 0, 'L', 0);
            // $this->pdf->Ln(0.6);
            // $this->pdf->Cell(6.5, 0.7, '', '', 0, 'L', 0);
            // $this->pdf->MultiCell(6, 0.5, $r['customer_address'], '', 'L', 0);
            // $this->pdf->Cell(6.5, 0.7, '', '', 0, 'L', 0);
            // $this->pdf->Cell(6, 0.7, 'Kec. ' . $r['customer_district'] . ', ' . $r['customer_city'], '', 0, 'L', 0);
            // $this->pdf->Ln(0.5);
            // $this->pdf->Cell(6.5, 0.7, '', '', 0, 'L', 0);
            // $this->pdf->Cell(6, 0.7, '' . $r['customer_province'] . '  -  ' . $r['customer_phone'], '', 0, 'L', 0);

            // $this->pdf->SetY($hy);
            // $this->pdf->Cell(13, 0.7, '', '', 0, 'L', 0);
            // $this->pdf->SetFont('Arial','B', 10);
            // $this->pdf->Cell(6, 0.7, 'Alamat Pengiriman : ', '', 0, 'L', 0);
            // $this->pdf->Ln(0.5);
            // $this->pdf->SetFont('Arial','', 10);
            // $this->pdf->Cell(13, 0.7, '', '', 0, 'L', 0);
            // $this->pdf->Cell(6, 0.7, '' . $r['sh_customer_name'], '', 0, 'L', 0);
            // $this->pdf->Ln(0.6);
            // $this->pdf->Cell(13, 0.7, '', '', 0, 'L', 0);
            // $this->pdf->MultiCell(6, 0.5, $r['sh_customer_address'], '', 'L', 0);
            // $this->pdf->Cell(13, 0.7, '', '', 0, 'L', 0);
            // $this->pdf->Cell(6, 0.7, 'Kec. ' . $r['sh_customer_district'] . ', ' . $r['sh_customer_city'], '', 0, 'L', 0);
            // $this->pdf->Ln(0.5);
            // $this->pdf->Cell(13, 0.7, '', '', 0, 'L', 0);
            // $this->pdf->Cell(6, 0.7, '' . $r['sh_customer_province'] . '  -  ' . $r['sh_customer_phone'], '', 0, 'L', 0);

            // $this->pdf->Ln(2.1);
            // $this->tableHeader($this->pdf);
            
            // $n = 0;
            // $sub = 0;
            // $this->pdf->SetTextColor(0,0,0);
            // foreach ($data as $k => $v)
            // {
            //     $n = $k;
            //     if ($k % 2 == 0)
            //         $this->pdf->SetFillColor(255,255,255);
            //     else
            //         $this->pdf->SetFillColor(220,220,220);
            //     $price = $v['item_price'] - $v['item_disctotal'];
            //     $this->pdf->Cell(8, 1, '  '.$v['item_name'], 'LB', 0, 'L', 1);
            //     $this->pdf->Cell(3, 1, $v['item_app_qty'], 'LB', 0, 'C', 1);
            //     $this->pdf->Cell(4, 1, 'Rp '.number_format($price).'  ', 'LB', 0, 'R', 1);
            //     $this->pdf->Cell(4, 1, 'Rp '.number_format($v['item_app_qty'] * $price).'  ', 'LBR', 0, 'R', 1);
            //     $this->pdf->Ln(1);

            //     $sub += $price * $v['item_app_qty'];
            // }
            // $n++;

            // // Sub Total
            // if ($n % 2 == 0)
            //     $this->pdf->SetFillColor(255,255,255);
            // else
            //     $this->pdf->SetFillColor(220,220,220);
            // $this->pdf->Cell(15, 1, 'Subtotal  ', 'LB', 0, 'R', 1);
            // $this->pdf->Cell(4, 1, 'Rp '.number_format($sub).'  ', 'LBR', 0, 'R', 1);
            // $this->pdf->Ln(1);
            // $n++;

            // // Biaya Kirim
            // if ($n % 2 == 0)
            //     $this->pdf->SetFillColor(255,255,255);
            // else
            //     $this->pdf->SetFillColor(220,220,220);
            // $this->pdf->Cell(15, 1, 'Biaya Pengiriman  ', 'LB', 0, 'R', 1);
            // $this->pdf->Cell(4, 1, 'Rp '.number_format($r['expedition_cost']).'  ', 'LBR', 0, 'R', 1);
            // $this->pdf->Ln(1);
            // $n++;

            // // Angka Unik
            // if ($r['unique_cost'] > 0)
            // {
            //     if ($n % 2 == 0)
            //         $this->pdf->SetFillColor(255,255,255);
            //     else
            //         $this->pdf->SetFillColor(220,220,220);
            //     $this->pdf->Cell(15, 1, 'Angka Unik  ', 'LB', 0, 'R', 1);
            //     $this->pdf->Cell(4, 1, '- Rp '.number_format($r['unique_cost']).'  ', 'LBR', 0, 'R', 1);
            //     $this->pdf->Ln(1);
            //     $n++;
            // }
            

            // // Coupon
            // if ($r['coupon_amount'] > 0)
            // {
            //     if ($n % 2 == 0)
            //         $this->pdf->SetFillColor(255,255,255);
            //     else
            //         $this->pdf->SetFillColor(220,220,220);
            //     $this->pdf->Cell(15, 1, 'Kupon Potongan [ '.$r['coupon_code'].' ]', 'LB', 0, 'R', 1);
            //     $this->pdf->Cell(4, 1, '- Rp '.number_format($r['coupon_amount']).'  ', 'LBR', 0, 'R', 1);
            //     $this->pdf->Ln(1);
            //     $n++;
            // }
            
            // // COD
            // if ($r['is_cod'] == "Y")
            // {
            //     if ($n % 2 == 0)
            //         $this->pdf->SetFillColor(255,255,255);
            //     else
            //         $this->pdf->SetFillColor(220,220,220);
            //     $this->pdf->Cell(15, 1, 'Biaya COD', 'LB', 0, 'R', 1);
            //     $this->pdf->Cell(4, 1, 'Rp '.number_format($r['cod_cost']).'  ', 'LBR', 0, 'R', 1);
            //     $this->pdf->Ln(1);
            //     $n++;
            // }

            // // Grand Total
            // $this->pdf->SetFont('Arial','B', 10);
            // if ($n % 2 == 0)
            //     $this->pdf->SetFillColor(255,255,255);
            // else
            //     $this->pdf->SetFillColor(220,220,220);
            // $this->pdf->Cell(15, 1, 'Grand Total  ', 'LB', 0, 'R', 1);
            // $this->pdf->Cell(4, 1, 'Rp '.number_format($r['grand_total']).'  ', 'LBR', 0, 'R', 1);
            // $this->pdf->Ln(1);
            // $n++;


            // $this->pdf->SetFont('Arial','B',12);
        // $this->print_grandtotal($this, $grand_total);
        }

        // if ($r['payment_code'] == 'IPAYMU.CS')
        // {
        //     $this->ipaymu_cs($this->pdf, $r);
        // }

        // if ($r['payment_code'] == 'IPAYMU.QRIS')
        // {
        //     $this->ipaymu_qris($this->pdf, $r);
        // }

        // $this->pdf->Output('/home/one/Web/uploads/invoices/'.$r['order_number'].'.pdf', 'F');
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

    function tableHeader($me, $d)
    {
        $wQty = $this->wQty;
        $wItemName = $me->w - $me->lMargin - $me->rMargin - 6 - ($wQty*(sizeof($d)+1));
        $this->pdf->SetFont('Arial','', 9);
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->Cell(4, 1, 'KODE' , 'LTBR', 0, 'C', 1);
        $me->Cell($wItemName, 1, 'NAMA ITEM' , 'LTBR', 0, 'C', 1);
        foreach ($d as $k => $v)
            $me->Cell($wQty, 1, $v['warehouse_short_name'] , 'LTBR', 0, 'R', 1);
        $me->Cell($wQty, 1, 'TOTAL' , 'LTBR', 0, 'R', 1);
        $me->Cell(2, 1, 'UNIT' , 'LTBR', 0, 'C', 1);

        // $me->Cell(4, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
        $me->Ln(1);
    }

    
}
?>

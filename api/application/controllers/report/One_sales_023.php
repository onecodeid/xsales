<?php

// Report Fee / Komisi Per Admin
//

class One_sales_023 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'SALES-023';
        setlocale(LC_ALL, 'id_ID');
    }

    function index() {
        $adjustment = 0.5;

        // Get data
        // --------
        $this->load->model('report/r_report');
        $r = $this->r_report->one_sales_023( $this->input->get('id'), $this->input->get('uid') );

        $data = (array) json_decode($r->items);
        foreach ($data as $k => $v)
            $data[$k] = (array) $v;
        // --------------
        // End of Get data

        $data_max_a5 = 6;
        // restructuring data
        $datas = [];
        foreach ($data as $k => $v)
        {
            $x = floor($k/$data_max_a5);
            if (!isset($datas[$x])) $datas[$x] = [];
            $datas[$x][] = $v;
        }

        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true, 1);
        $size = ["L","A5"];
        // if (sizeof($data) > $data_max_a5)
        //     $size = ["P", "A4"];
        // if (isset($this->sys_input['size']))
        //     $size = (array) json_decode($this->sys_input['size']);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        // $this->pdf->header_func = "my_header";
        // $this->pdf->footer_func = "my_footer";

        $this->pdf->SetMargins(0.7, 0.5+$adjustment, 0.7);
        $this->pdf->SetAutoPageBreak(true, 0.5);

        foreach ($datas as $kkk => $vvv)
        {
            $data = $vvv;

            $this->pdf->AddPage($size[0], $size[1]);
            $this->pdf->SetFont('Arial','', 11);
            if (!is_array($r->customer_phones))
                $r->customer_phones = json_decode($r->customer_phones);

            // Phones
            $hdr_data = [];
            $sales_phones = json_decode($r->sales_phones);
            if (sizeof($sales_phones)>0)
            {
                $phones = [];
                foreach ($sales_phones as $k => $v) $phones[] = $v->no;
                $hdr_data['phone'] = join(', ', $phones);
            }

            // Email
            if ($r->sales_email != "") $hdr_data['email'] = $r->sales_email;
            $this->pdf->setHdrData($hdr_data);
            
            $title = 'INVOICE';
            $this->pdf->SetTextColor(0,0,0);
            $this->my_header_a5($this, 
                    $title, 
                    '');


            $fy = $this->pdf->GetY()+$adjustment;
            $this->pdf->Ln(-0.2+$adjustment);
            $this->pdf->Cell(11.6, 2.3, '', 'LBR', 0, 'L');
            $this->pdf->Cell(8, 2.3, '', 'LBR', 0, 'L');

            $total_n = 7;
            if ($r->sales_disc == 0 && $r->sales_discrp == 0) $total_n--;
            if ($r->sales_shipping == 0) $total_n--;
            if ($r->sales_dp == 0) $total_n--;

            $base_after_item = 5.1 + (sizeof($data)*0.5) + 0.4+$adjustment;
            $ack_h = 1.2;
            $total_h = ($total_n*0.5)+0.2;

            $this->pdf->SetY($base_after_item);
            $this->pdf->Cell(12.6, $total_h+$ack_h, '', 'LBTR', 0, 'L');
            $this->pdf->Cell(7, $total_h+$ack_h, '', 'TBR', 0, 'L');
            $this->pdf->SetY($base_after_item+$total_h);
            $this->pdf->Cell(9.1, 0.5, '', '', 0, 'L');

            $customer = "<b>{$r->customer_name}</b>";
            if ($r->customer_pic != "") $customer .= " (PIC : <b>{$r->customer_pic}</b>)";

            $this->pdf->SetY($fy);
            $this->pdf->SetFont('Arial','', 9);
            $rm = $this->pdf->rMargin;
            
            $this->pdf->Cell(3, 0.5, "No Transaksi");
            $this->pdf->Cell(0.5, 0.5, ":");
            $this->pdf->Cell(3.9, 0.5, $r->sales_number, "", 0, "L");
            $this->pdf->Ln(0.5);
            $this->pdf->Cell(3, 0.5, "Tanggal");
            $this->pdf->Cell(0.5, 0.5, ":");
            $this->pdf->Cell(3.9, 0.5, date('d/m/Y', strtotime($r->sales_date)), "", 0, "L");
            $this->pdf->Ln(0.5);
            $this->pdf->Cell(3, 0.5, "Nama");
            $this->pdf->Cell(0.5, 0.5, ":");
            $this->pdf->Cell(3.9, 0.5, $r->customer_name, "", 0, "L");
            $this->pdf->Ln(0.5);
            $this->pdf->Cell(3, 0.5, "Alamat");
            $this->pdf->Cell(0.5, 0.5, ":");
            $this->pdf->Cell(3.9, 0.5, $r->customer_address, "", 0, "L");
            $this->pdf->Ln(0.5);

            $this->pdf->SetY($fy);
            $this->pdf->SetX(12.6);
            $this->pdf->Cell(3, 0.5, "Pembayaran");
            $this->pdf->Cell(0.5, 0.5, ":");
            $this->pdf->Cell(3.9, 0.5, $r->term_name, "", 0, "R");
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(12.6);
            $this->pdf->Cell(3, 0.5, "Lama Kredit");
            $this->pdf->Cell(0.5, 0.5, ":");
            $this->pdf->Cell(3.9, 0.5, $r->term_duration, "", 0, "R");
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(12.6);
            $this->pdf->Cell(3, 0.5, "Jatuh Tempo");
            $this->pdf->Cell(0.5, 0.5, ":");
            $this->pdf->Cell(3.9, 0.5, date('d F Y', strtotime($r->sales_due_date)), "", 0, "R");

            // FOOTER
            $this->pdf->SetY($base_after_item+0.1);

            $this->pdf->SetStyle('B', false);
            $this->pdf->Cell(3.5, 0.5, 'Dibuat,', '', 0, 'C');
            $this->pdf->Cell(3.5, 0.5, 'Mengetahui,', '', 0, 'C');
            $this->pdf->Cell(3.5, 0.5, 'Penerima,', '', 0, 'C');
            $this->pdf->Ln(2.5);
            $this->pdf->SetStyle('B', true);
            $this->pdf->Cell(3.5, 0.5, "( ........................... )", '', 0, 'C');
            $this->pdf->Cell(3.5, 0.5, "( ........................... )", '', 0, 'C');
            $this->pdf->Cell(3.5, 0.5, "( ........................... )", '', 0, 'C');
            
            // DATA
            $this->pdf->SetY(4.4+$adjustment);
            $this->pdf->Cell(1.5, 0.7, 'NO.', 'LTB', 0, 'C');
            $this->pdf->Cell(1.5, 0.7, 'KODE', 'TB', 0, 'L');
            $this->pdf->Cell(5.8, 0.7, 'NAMA ITEM', 'TB', 0, 'L');
            
            $this->pdf->Cell(1.5, 0.7, 'QTY', 'TB', 0, 'R');
            $this->pdf->Cell(2, 0.7, 'HARGA', 'TB', 0, 'R');
            $this->pdf->Cell(2.5, 0.7, 'BRUTO', 'TB', 0, 'R');
            $this->pdf->Cell(2, 0.7, 'POT.', 'TB', 0, 'R');
            $this->pdf->Cell(2.5, 0.7, 'NETTO', 'TB', 0, 'R');
            $this->pdf->Cell(0.3, 0.7, '', 'TBR', 0, 'R');
            $this->pdf->Ln(0.7);

            $total = 0;
            $this->pdf->SetStyle('B', false);
            $item_h = 0.5;
            $this->pdf->Ln(0.2);

            $totals = ['disc'=>0, 'bruto'=>0, 'netto'=>0];
            foreach ($data as $k => $v)
            {
                $bruto = $v['item_price']*$v['item_qty'];
                $disc = $v['item_disc']*$bruto/100;
                $netto = $v['item_subtotal'];

                $this->pdf->Cell(1.5, $item_h, $k+1, '', 0, 'C');
                $this->pdf->Cell(1.5, $item_h, $v['item_code'], '', 0, 'L');
                $this->pdf->Cell(5.8, $item_h, $v['item_name'], '', 0, 'L');
                
                $this->pdf->Cell(1.5, $item_h, number_format($v['item_qty'], 0), '', 0, 'R');
                $this->pdf->Cell(2, $item_h, number_format($v['item_price'], 0), '', 0, 'R');
                $this->pdf->Cell(2.5, $item_h, number_format($bruto, 0), '', 0, 'R');
                $this->pdf->Cell(2, $item_h, number_format($disc, 0), '', 0, 'R');
                $this->pdf->Cell(2.5, $item_h, number_format($netto, 0), '', 0, 'R');
                $this->pdf->Cell(0.3, $item_h, '', '', 0, 'R');
                $this->pdf->Ln($item_h);

                $total += $v['item_subtotal'];
                $totals['disc'] += $disc;
                $totals['bruto'] += $bruto;
                $totals['netto'] += $netto;
            }
            // $total = round($total);

            $r->sales_total = round($r->sales_total);
            $this->pdf->SetY($base_after_item+0.1);
            $this->pdf->SetX(10.1);
            $this->pdf->Cell(2.9, 0.5, '', '', 0, 'L');
            $this->pdf->SetStyle('B', true);
            $this->pdf->Cell(2.5, 0.5, number_format($totals['bruto']), '', 0, 'R');
            $this->pdf->Cell(2, 0.5, number_format($totals['disc']), '', 0, 'R');
            $this->pdf->Cell(2.5, 0.5, number_format($totals['netto']), '', 0, 'R');

            $discrp = $r->sales_discrp;
            if ($discrp == 0 && $r->sales_disc > 0)
                $discrp = $r->sales_subtotal * $r->sales_disc / 100;

            if ($discrp > 0)
            {
                $this->pdf->Ln(0.5);
                $this->pdf->SetX(10.1);
                $this->pdf->SetFont('Arial','');
                $this->pdf->Cell(2.9, 0.5, 'Potongan', '', 0, 'L');
                $this->pdf->Cell(3.5, 0.5, $r->sales_disc > 0 ? '('.number_format($r->sales_disc) .'%)':'', '', 0, 'R');
                $this->pdf->Cell(3.5, 0.5, "(".number_format($discrp).")", '', 0, 'R');

                $total = $total - $discrp;
                $this->pdf->Ln(0.5);
                $this->pdf->SetX(10.1);
                $this->pdf->Cell(2.9, 0.5, 'Total', '', 0, 'L');
                $this->pdf->SetStyle('B', true);
                $this->pdf->Cell(7, 0.5, number_format($total), '', 0, 'R');
            }

            if ($r->sales_ppn > 0)
            {
                $this->pdf->Ln(0.5);
                $this->pdf->SetX(10.1);
                $this->pdf->SetFont('Arial','');
                $this->pdf->Cell(2.9, 0.5, 'PPN', '', 0, 'L');
                $this->pdf->Cell(7, 0.5, number_format($r->sales_ppn), '', 0, 'R');
            }

            if ($r->sales_shipping > 0)
            {
                $this->pdf->Ln(0.5);
                $this->pdf->SetX(10.1);
                $this->pdf->SetFont('Arial','');
                $this->pdf->Cell(2.9, 0.5, 'Biaya Pengiriman', '', 0, 'L');
                $this->pdf->Cell(3.5, 0.5, '', '', 0, 'R');
                $this->pdf->Cell(3.5, 0.5, number_format($r->sales_shipping), '', 0, 'R');
            }

            if ($r->sales_dp > 0)
            {
                $this->pdf->Ln(0.5);
                $this->pdf->SetX(10.1);
                $this->pdf->SetFont('Arial','');
                $this->pdf->Cell(6.4, 0.5, 'Uang Muka', '', 0, 'L');
                $this->pdf->Cell(3.5, 0.5, " (".number_format($r->sales_dp).")", '', 0, 'R');
            }

            $this->pdf->Ln(0.7);
            $this->pdf->SetX(13.6);
            $this->pdf->SetStyle('B', true);
            $this->pdf->Cell(2.9, 0.5, 'Grand Total', '', 0, 'L');
            
            $this->pdf->Cell(3.5, 0.5, number_format($total - $r->sales_dp + $r->sales_shipping + $r->sales_ppn), '', 0, 'R');

            $this->pdf->Ln(1);
            $this->pdf->SetX(13.6);
            $this->pdf->SetStyle('B', false);
            $this->pdf->SetStyle('I', true);
            $this->pdf->MultiCell(6.8, 0.5, 'Terbilang : '.ucwords($r->terbilang) . ' Rupiah', '', 'L');
            $this->pdf->SetStyle('I', false);
        }
        

        $this->pdf->Output('Invoice_' . explode("/", $r->sales_number)[0] . '_' . $r->customer_name . '.pdf', 'I');
    }

    function tableHeader($me)
    {
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $this->pdf->SetFont('Arial','', 10);
        $me->Cell(1, 1, $me->GetY().'NO' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(5, 1, 'CUSTOMER' , 'LTBR', 0, 'C', 1);
        $me->Cell(5, 1, 'NAMA ITEM' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'HARGA' , 'LTBR', 0, 'C', 1);
        $me->Cell(1, 1, 'QTY' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'SUBTOTAL' , 'LTBR', 0, 'C', 1);

        $me->Ln(1);
    }
}
?>

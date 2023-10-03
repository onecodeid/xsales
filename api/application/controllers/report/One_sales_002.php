<?php

// Report Fee / Komisi Per Admin
//

class One_sales_002 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'SALES-002';
        setlocale(LC_ALL, 'id_ID');
    }

    function index() {
        // Get data
        // --------
        $this->load->model('report/r_report');
        if (isset($this->sys_input['proforma']))
            $r = $this->r_report->one_sales_002_proforma( $this->input->get('id'), $this->input->get('uid') );
        else
            $r = $this->r_report->one_sales_002( $this->input->get('id'), $this->input->get('uid') );

        $data = (array) json_decode($r->items);
        foreach ($data as $k => $v)
            $data[$k] = (array) $v;
        // --------------
        // End of Get data

        $data_max_a5 = 2;
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true, 1);
        $size = ["L","A5"];
        if (sizeof($data) > $data_max_a5)
            $size = ["P", "A4"];
        if (isset($this->sys_input['size']))
            $size = (array) json_decode($this->sys_input['size']);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        // $this->pdf->header_func = "my_header";
        // $this->pdf->footer_func = "my_footer";

        $this->pdf->SetMargins(0.7, 0.5, 0.7);
        $this->pdf->SetAutoPageBreak(true, 0.5);
        $this->pdf->AddPage($size[0], $size[1]);
        $this->pdf->SetFont('Arial','', 11);
        
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
        
        $title = isset($this->sys_input['proforma']) ? 'PROFORMA INVOICE' : 'INVOICE';
        $this->pdf->SetTextColor(0,0,0);
        $this->my_header_a5($this, 
                $title, 
                '');

        $fy = $this->pdf->GetY();
        $this->pdf->Ln(-0.2);
        $this->pdf->Cell(11.6, 2.8, '', 'LBR', 0, 'L');
        $this->pdf->Cell(8, 2.8, '', 'LBR', 0, 'L');

        $total_n = 7;
        if ($r->invoice_disc == 0 && $r->invoice_discrp == 0) $total_n--;
        if ($r->invoice_shipping == 0) $total_n--;
        if ($r->invoice_dp == 0) $total_n--;

        $base_after_item = 6.7 + (sizeof($data)*0.5) + 0.4;
        $ack_h = 2.7;
        $total_h = ($total_n*0.5)+0.2;

        $this->pdf->SetY($base_after_item);
        $this->pdf->Cell(9.1, $total_h+$ack_h, '', 'LBTR', 0, 'L');
        $this->pdf->Cell(10.5, $total_h, '', 'TBR', 0, 'L');
        $this->pdf->SetY($base_after_item+$total_h);
        $this->pdf->Cell(9.1, 0.5, '', '', 0, 'L');
        $this->pdf->Cell(3.5, $ack_h, '', 'BR', 0, 'L');
        $this->pdf->Cell(3.5, $ack_h, '', 'BR', 0, 'L');
        $this->pdf->Cell(3.5, $ack_h, '', 'BR', 0, 'L');


        $customer = "<b>{$r->customer_name}</b>";
        if ($r->customer_pic != "") $customer .= " (PIC : <b>{$r->customer_pic}</b>)";

        $this->pdf->SetY($fy);
        $this->pdf->SetFont('Arial','', 9);
        $rm = $this->pdf->rMargin;
        $this->pdf->SetRightMargin($this->pdf->w - $this->pdf->GetX() - (9.5+1.8));
        $this->pdf->WriteHTML("To : {$customer}", 0.5);
        $this->pdf->SetRightMargin($rm);
        $this->pdf->Ln(0.5);
        // $this->pdf->WriteHTML("Alamat : Perumahan Kota Wisata", 0.5);
        // $this->pdf->Ln(0.45);
        // $this->pdf->WriteHTML("Jl. Boulevard Utama Blok I 1/28 (Depan Bank BCA)", 0.5);
        // $this->pdf->MultiCell(10.8, 0.5, "Alamat : " . $r->customer_address);

        $this->pdf->SetRightMargin($this->pdf->w - $this->pdf->GetX() - (9.5+1.8));
        $this->pdf->WriteHTML( "Alamat : " . $r->customer_address . " - " .
            ($r->district_name != "" && $r->district_name != null ? $r->district_name . ", " : "") .
            ($r->city_name != "" && $r->city_name != null ? $r->city_name . ", " : "") .
            $r->province_name . " ", 0.5);
        // $this->pdf->Ln(0.45);

        // PHONES
        $phs = [];
        foreach ($r->customer_phones as $l => $w)
            $phs[] = "+62 ".$w->no;
        $this->pdf->WriteHTML("<b>".join(', ', $phs)."</b>", 0.5);

        $this->pdf->SetRightMargin($rm);
        // $this->pdf->WriteHTML("Alamat : " . $r->customer_address, 0.5);
        // $this->pdf->Ln(0.45);
        // $this->pdf->WriteHTML("Cibubur, Gunung Putri, Bogor", 0.5);
        // $this->pdf->Ln(0.7);
        // $this->pdf->WriteHTML("<b>+62 898 5945 837</b>", 0.5);

        $this->pdf->SetY($fy);
        $this->pdf->SetX(12.6);
        $this->pdf->Cell(3, 0.5, "No");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(3.9, 0.5, $r->invoice_number, "", 0, "R");
        $this->pdf->Ln(0.5);
        $this->pdf->SetX(12.6);
        $this->pdf->Cell(3, 0.5, "No DO");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(3.9, 0.5, $r->deliveries, "", 0, "R");
        $this->pdf->Ln(0.5);
        $this->pdf->SetX(12.6);
        $this->pdf->Cell(3, 0.5, "KS");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(3.9, 0.5, $r->sales_code, "", 0, "R");
        $this->pdf->Ln(0.5);
        $this->pdf->SetX(12.6);
        $this->pdf->Cell(3, 0.5, "Tgl. Tagihan");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(3.9, 0.5, date('d F Y', strtotime($r->invoice_date)), "", 0, "R");
        $this->pdf->Ln(0.5);
        $this->pdf->SetX(12.6);
        $this->pdf->Cell(3, 0.5, "Tgl. Jatuh Tempo");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(3.9, 0.5, date('d F Y', strtotime($r->invoice_due_date)), "", 0, "R");

        // FOOTER
        $this->pdf->SetY($base_after_item+0.1);
        // $this->pdf->SetStyle('B', true);
        // $this->pdf->WriteHTML("Pengirim : <b>{$r->admin_name}</b>");
        // $this->pdf->Ln(0.7);
        $rm = $this->pdf->rMargin;
        $h = 0.5;
        $this->pdf->SetRightMargin($this->pdf->w - $this->pdf->GetX() - 9);

        $this->pdf->SetStyle('B', true);
        $this->pdf->WriteHtml("Pesan : ", $h);
        $this->pdf->SetStyle('B', false);
        $this->pdf->WriteHtml($r->invoice_note, $h);
        $this->pdf->SetRightMargin($rm);


        // $this->pdf->SetStyle('B', false);
        // $this->pdf->WriteHTMLCell(9, "<b>Pesan</b> : " . $r->invoice_note, 0.6);
        // $this->pdf->SetStyle('B', false);
        $this->pdf->SetFont('Arial','', 9);
        
        $this->pdf->Ln(0.45);
        $this->pdf->Cell(1.45,0.5, '');
        // $this->pdf->SetY($this->pdf->GetY()+0.2);
        // $this->pdf->Ln(0.7);
        // $this->pdf->MultiCell(7.3, 0.5, $r->invoice_note, "", "L");
        // $this->pdf->Ln(0.5);

        // PATCH
        $this->pdf->Ln(-0.2);

        $this->pdf->SetStyle('B', true);
        $this->pdf->WriteHTML("Pembayaran : ");
        $this->pdf->SetStyle('B', false);
        $this->pdf->WriteHTML($r->term_name);
        $this->pdf->Ln(0.5);

        $this->pdf->SetStyle('B', true);
        $this->pdf->WriteHTML("Transfer ke :");
        $this->pdf->Ln(0.7);
        
        
        $this->pdf->SetStyle('B', false);
        $this->pdf->SetFont('Arial','', 9);
        $banks = json_decode($r->banks);
        foreach ($banks as $k => $v)
        {
            $this->pdf->MultiCell(8.8, 0.5, "{$v->bank_name} a/n {$v->account_name} {$v->account_number}", "", "L");
        }

        // PATCH
        $this->pdf->Ln(-0.2);

        $this->pdf->SetStyle('B', true);
        $this->pdf->WriteHTML("Terbilang :");
        $this->pdf->Ln(0.7);
        $this->pdf->SetStyle('B', false);
        $this->pdf->SetFont('Arial','', 9);
        $this->pdf->MultiCell(8.8, 0.5, ucwords($r->terbilang) . ' Rupiah', "", "L");

        $this->pdf->SetY($base_after_item+$total_h+0.1);
        $this->pdf->SetX(9.8);
        $this->pdf->SetStyle('B', false);
        $this->pdf->Cell(3.5, 0.5, 'Acknowledge,', '', 0, 'C');
        $this->pdf->Cell(3.5, 0.5, 'Admin By,', '', 0, 'C');
        $this->pdf->Cell(3.5, 0.5, 'Customer,', '', 0, 'C');

        $this->pdf->SetY($base_after_item+$total_h+$ack_h-0.6);
        $this->pdf->SetX(9.8);
        $this->pdf->SetStyle('B', true);
        $this->pdf->Cell(3.5, 0.5, $r->ack_name, '', 0, 'C');
        $this->pdf->Cell(3.5, 0.5, $r->admin_name, '', 0, 'C');
        $this->pdf->Cell(3.5, 0.5, "( .................................. )", '', 0, 'C');

        // DATA
        $this->pdf->SetY(6);
        $this->pdf->Cell(1.5, 0.7, 'NO.', 'LTB', 0, 'C');
        $this->pdf->Cell(7.8, 0.7, 'NAMA ITEM', 'TB', 0, 'L');
        $this->pdf->Cell(1.5, 0.7, 'UNIT', 'TB', 0, 'L');
        $this->pdf->Cell(1.5, 0.7, 'QTY', 'TB', 0, 'R');
        $this->pdf->Cell(2.5, 0.7, 'HARGA', 'TB', 0, 'R');
        $this->pdf->Cell(2, 0.7, 'POT.', 'TB', 0, 'R');
        $this->pdf->Cell(2.5, 0.7, 'SUBTOTAL', 'TB', 0, 'R');
        $this->pdf->Cell(0.3, 0.7, '', 'TBR', 0, 'R');
        $this->pdf->Ln(0.7);

        $total = 0;
        $this->pdf->SetStyle('B', false);
        $item_h = 0.5;
        $this->pdf->Ln(0.2);
        foreach ($data as $k => $v)
        {
            $this->pdf->Cell(1.5, $item_h, $k+1, '', 0, 'C');
            $this->pdf->Cell(7.8, $item_h, $v['item_name'], '', 0, 'L');
            $this->pdf->Cell(1.5, $item_h, $v['item_unit'], '', 0, 'L');
            $this->pdf->Cell(1.5, $item_h, number_format($v['item_qty'], 2), '', 0, 'R');
            $this->pdf->Cell(2.5, $item_h, number_format($v['item_price'], 2), '', 0, 'R');
            $this->pdf->Cell(2, $item_h, number_format($v['item_disc'], 2), '', 0, 'R');
            $this->pdf->Cell(2.5, $item_h, number_format($v['item_subtotal'], 2), '', 0, 'R');
            $this->pdf->Cell(0.3, $item_h, '', '', 0, 'R');
            $this->pdf->Ln($item_h);

            $total += $v['item_subtotal'];
        }
        // $total = round($total);

        $r->invoice_total = round($r->invoice_total);
        $this->pdf->SetY($base_after_item+0.1);
        $this->pdf->SetX(10.1);
        $this->pdf->Cell(2.9, 0.5, 'Sub Total', '', 0, 'L');
        $this->pdf->SetStyle('B', true);
        $this->pdf->Cell(7, 0.5, number_format(isset($r->invoice_subtotal)?$r->invoice_subtotal:$r->invoice_total, 2), '', 0, 'R');

        $discrp = $r->invoice_discrp;
        if ($discrp == 0 && $r->invoice_disc > 0)
            $discrp = $r->invoice_subtotal * $r->invoice_disc / 100;

        if ($discrp > 0)
        {
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(10.1);
            $this->pdf->SetFont('Arial','');
            $this->pdf->Cell(2.9, 0.5, 'Potongan', '', 0, 'L');
            $this->pdf->Cell(3.5, 0.5, $r->invoice_disc > 0 ? '('.number_format($r->invoice_disc, 2) .'%)':'', '', 0, 'R');
            $this->pdf->Cell(3.5, 0.5, "(".number_format($discrp, 2).")", '', 0, 'R');

            $total = $total - $discrp;
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(10.1);
            $this->pdf->Cell(2.9, 0.5, 'Total', '', 0, 'L');
            $this->pdf->SetStyle('B', true);
            $this->pdf->Cell(7, 0.5, number_format($total, 2), '', 0, 'R');
        }

        if ($r->invoice_ppn > 0)
        {
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(10.1);
            $this->pdf->SetFont('Arial','');
            $this->pdf->Cell(2.9, 0.5, 'PPN', '', 0, 'L');
            $this->pdf->Cell(7, 0.5, number_format($r->invoice_ppn, 2), '', 0, 'R');

            // $total = $total + $r->invoice_ppn;
            // $total = round($total);
            
            // $this->pdf->Ln(0.5);
            // $this->pdf->SetX(10.1);
            // $this->pdf->Cell(2.9, 0.5, 'Total', '', 0, 'L');
            // $this->pdf->SetStyle('B', true);
            // $this->pdf->Cell(7, 0.5, number_format($total, 2), '', 0, 'R');
        }

        if ($r->invoice_shipping > 0)
        {
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(10.1);
            $this->pdf->SetFont('Arial','');
            $this->pdf->Cell(2.9, 0.5, 'Biaya Pengiriman', '', 0, 'L');
            $this->pdf->Cell(3.5, 0.5, '', '', 0, 'R');
            $this->pdf->Cell(3.5, 0.5, number_format($r->invoice_shipping, 2), '', 0, 'R');
        }

        if ($r->invoice_dp > 0)
        {
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(10.1);
            $this->pdf->SetFont('Arial','');
            $this->pdf->Cell(6.4, 0.5, 'Uang Muka', '', 0, 'L');
            $this->pdf->Cell(3.5, 0.5, " (".number_format($r->invoice_dp, 2).")", '', 0, 'R');
        }

        $this->pdf->Ln(0.5);
        $this->pdf->SetX(10.1);
        $this->pdf->Cell(2.9, 0.5, 'Grand Total', '', 0, 'L');
        $this->pdf->SetStyle('B', true);
        $this->pdf->Cell(7, 0.5, number_format($total - $r->invoice_dp + $r->invoice_shipping + $r->invoice_ppn, 2), '', 0, 'R');

        $this->pdf->Output('Invoice_' . explode("/", $r->invoice_number)[0] . '_' . $r->customer_name . '.pdf', 'I');
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

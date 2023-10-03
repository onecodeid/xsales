<?php

// Report Fee / Komisi Per Admin
//

class One_purchase_001 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'PURCHASE-001';
    }

    function index() {
        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_purchase_001( $this->input->get('id'), $this->sys_user['user_id'] );
        // $r->vendor_phones = json_decode($r->vendor_phones);

        $data = (array) json_decode($r->items);
        foreach ($data as $k => $v)
            $data[$k] = (array) $v;
            
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true, 1);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        // $this->pdf->header_func = "my_header";
        // $this->pdf->footer_func = "my_footer";

        $this->pdf->SetMargins(0.7, 0.7, 0.7);
        $size = ["L","A5"];
        if (isset($this->sys_input['size']))
            $size = (array) json_decode($this->sys_input['size']);
        
        if (sizeof($data) >= 4)
            $size = ["P","A4"];
        
        $this->pdf->AddPage($size[0], $size[1]);
        $h = $this->pdf->h;

        $this->pdf->SetFont('Arial','', 11);
        
        $this->pdf->SetTextColor(0,0,0);
        $this->my_header_a5($this, 
                'PURCHASE ORDER', 
                '');

        

        $fy = $this->pdf->GetY();
        $this->pdf->Cell(9.8, 2.6, '', 'LBR', 0, 'L');
        $this->pdf->Cell(9.8, 2.6, '', 'LBR', 0, 'L');

        $this->pdf->SetY($h-5.88);
        // $this->pdf->SetY(9);
        $this->pdf->Cell(6, 4.8, '', 'LBTR', 0, 'L');
        $this->pdf->Cell(13.6, 2.7, '', 'TBR', 0, 'L');
        $this->pdf->SetY($h-3.18);
        // $this->pdf->SetY(11.7);
        $this->pdf->Cell(6, 0.5, '', '', 0, 'L');
        $this->pdf->Cell(4.5, 2.1, '', 'BR', 0, 'L');
        $this->pdf->Cell(4.5, 2.1, '', 'BR', 0, 'L');
        $this->pdf->Cell(4.6, 2.1, '', 'BR', 0, 'L');


        $vendor = "<b>{$r->vendor_name}</b>";

        $this->pdf->SetY($fy);
        $this->pdf->SetFont('Arial','', 9);
        $this->pdf->WriteHTML("Kepada : {$vendor}", 0.5);
        $this->pdf->Ln(0.5);

        $this->pdf->MultiCell(9, 0.5, "Alamat : " . $r->vendor_address);
        // $this->pdf->WriteHTML("{$r->district_name}, {$r->city_name}, {$r->province_name}", 0.5);
        // $this->pdf->Ln(0.45);

        // PHONES
        $r->vendor_phones = json_decode(json_encode([["no"=>$r->vendor_phone,"wa"=>"N"]]));
        $phs = [];
        foreach ($r->vendor_phones as $l => $w)
            $phs[] = $this->phone_format($w->no);
        $this->pdf->WriteHTML("<b>".join(', ', $phs)."</b>", 0.5);
        // $this->pdf->WriteHTML("Alamat : " . $r->vendor_address, 0.5);
        // $this->pdf->Ln(0.45);
        // $this->pdf->WriteHTML("Cibubur, Gunung Putri, Bogor", 0.5);
        // $this->pdf->Ln(0.7);
        // $this->pdf->WriteHTML("<b>+62 898 5945 837</b>", 0.5);

        $this->pdf->SetY($fy);
        $this->pdf->SetX(10.8);
        $this->pdf->Cell(3, 0.5, "No");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(5.7, 0.5, $r->purchase_number, "", 0, "R");
        $this->pdf->Ln(0.5);
        $this->pdf->SetX(10.8);
        $this->pdf->Cell(3, 0.5, "KS");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(5.7, 0.5, $r->sales_code, "", 0, "R");
        $this->pdf->Ln(0.5);
        $this->pdf->SetX(10.8);
        $this->pdf->Cell(3, 0.5, "Tgl. PO");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(5.7, 0.5, date('d F Y', strtotime($r->purchase_date)), "", 0, "R");
        $this->pdf->Ln(0.5);
        $this->pdf->SetX(10.8);
        $this->pdf->Cell(3, 0.5, "Pembayaran");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(5.7, 0.5, $r->paymentplan_name, "", 0, "R");

        // FOOTER
        $this->pdf->SetY($h-6.08);
        // $this->pdf->SetY(8.8);
        $this->pdf->WriteHTML("Pengirim : <b>{$r->admin_name}</b>");
        $this->pdf->Ln(0.7);
        $this->pdf->MultiCell(6, 0.7, "Catatan : ".$r->purchase_note, "", "L");
        // $this->pdf->WriteHTML("Catatan : ".$r->purchase_note);

        $this->pdf->SetY($h-3.08);
        // $this->pdf->SetY(11.8);
        $this->pdf->SetX(6.7);
        $this->pdf->SetStyle('B', false);
        $this->pdf->Cell(4.5, 0.5, 'Acknowledge,', '', 0, 'C');
        $this->pdf->Cell(4.5, 0.5, 'Admin By,', '', 0, 'C');
        $this->pdf->Cell(4.6, 0.5, 'Customer,', '', 0, 'C');

        $this->pdf->SetY($h-1.68);
        // $this->pdf->SetY(13.2);
        $this->pdf->SetX(6.7);
        $this->pdf->SetStyle('B', true);
        $this->pdf->Cell(4.5, 0.5, $r->ack_name, '', 0, 'C');
        $this->pdf->Cell(4.5, 0.5, $r->admin_name, '', 0, 'C');
        $this->pdf->Cell(4.6, 0.5, "( .................................. )", '', 0, 'C');

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
        foreach ($data as $k => $v)
        {
            $this->pdf->Cell(1.5, 0.7, $k+1, '', 0, 'C');
            $this->pdf->Cell(7.8, 0.7, $v['item_name'], '', 0, 'L');
            $this->pdf->Cell(1.5, 0.7, $v['item_unit'], '', 0, 'L');
            $this->pdf->Cell(1.5, 0.7, number_format($v['item_qty'], 2), '', 0, 'R');
            $this->pdf->Cell(2.5, 0.7, number_format($v['item_price'], 2), '', 0, 'R');
            $this->pdf->Cell(2, 0.7, number_format($v['item_disc'], 2), '', 0, 'R');
            $this->pdf->Cell(2.5, 0.7, number_format($v['item_subtotal'], 2), '', 0, 'R');
            $this->pdf->Cell(0.3, 0.7, '', '', 0, 'R');
            $this->pdf->Ln(0.7);

            $total += $v['item_total'];
        }

        $this->pdf->SetY($h-5.78);
        // $this->pdf->SetY(9.1);
        $this->pdf->SetX(7);
        $this->pdf->Cell(6, 0.5, 'Sub Total', '', 0, 'L');
        $this->pdf->SetStyle('B', true);
        $this->pdf->Cell(7, 0.5, number_format($r->purchase_subtotal, 2), '', 0, 'R');

        $total = $r->purchase_total;

        if ($r->purchase_ppn > 0)
        {
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(7);
            $this->pdf->SetFont('Arial','');
            $this->pdf->Cell(6, 0.5, 'PPN', '', 0, 'L');
            $this->pdf->Cell(7, 0.5, number_format($r->purchase_ppn, 2), '', 0, 'R');

            $total = $total + $r->purchase_ppn;
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(7);
            $this->pdf->Cell(6, 0.5, 'Total', '', 0, 'L');
            $this->pdf->SetStyle('B', true);
            $this->pdf->Cell(7, 0.5, number_format($total, 2), '', 0, 'R');
        }
        

        
        
        $discrp = 0;
        $discrp = $r->purchase_discrp;
        if ($discrp == 0 && $r->purchase_disc > 0)
            $discrp = $r->purchase_total * $r->purchase_disc / 100;

        if ($discrp > 0)
        {
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(7);
            $this->pdf->SetFont('Arial','');
            $this->pdf->Cell(6, 0.5, 'Potongan', '', 0, 'L');
            $this->pdf->Cell(3.5, 0.5, $r->purchase_disc > 0 ? '('.number_format($r->purchase_disc, 2) .'%)':'', '', 0, 'R');
            $this->pdf->Cell(3.5, 0.5, " - ".number_format($discrp, 2), '', 0, 'R');
        }

        if ($r->purchase_shipping > 0)
        {
            $total = $total + $r->purchase_shipping;
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(7);
            $this->pdf->SetFont('Arial','');
            $this->pdf->Cell(6, 0.5, 'Biaya Pengiriman', '', 0, 'L');
            $this->pdf->Cell(7, 0.5, number_format($r->purchase_shipping, 2), '', 0, 'R');
        }

        if ($r->purchase_dp > 0)
        {
            $this->pdf->Ln(0.5);
            $this->pdf->SetX(7);
            $this->pdf->SetFont('Arial','');
            $this->pdf->Cell(6, 0.5, 'Uang Muka', '', 0, 'L');
            $this->pdf->Cell(7, 0.5, " (".number_format($r->purchase_dp, 2).")", '', 0, 'R');
        }

        $this->pdf->Ln(0.5);
        $this->pdf->SetX(7);
        $this->pdf->Cell(6, 0.5, 'Grand Total', '', 0, 'L');
        $this->pdf->SetStyle('B', true);
        $this->pdf->Cell(7, 0.5, number_format($total - $discrp - $r->purchase_dp, 2), '', 0, 'R');

        $this->pdf->Output('Purchase_' . $r->purchase_number . '_' . $r->vendor_name . '.pdf', 'I');
    }

    function tableHeader($me)
    {
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $this->pdf->SetFont('Arial','', 10);
        $me->Cell(1, 1, 'NO' , 'LTBR', 0, 'C', 1);
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

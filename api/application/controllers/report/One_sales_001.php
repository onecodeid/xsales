<?php

// Report Fee / Komisi Per Admin
//

class One_sales_001 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'SALES-001';
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true, 1);
        $size = ["L","A5"];
        if (isset($this->sys_input['size']))
            $size = (array) json_decode($this->sys_input['size']);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_sales_001( $this->input->get('id') );
        $r->customer_phones = json_decode($r->customer_phones);

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        // $this->pdf->header_func = "my_header";
        // $this->pdf->footer_func = "my_footer";

        $this->pdf->SetMargins(0.7, 0.7, 0.7);
        $this->pdf->AddPage($size[0], $size[1]);
        $this->pdf->SetFont('Arial','', 11);
        

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

        $this->pdf->SetTextColor(0,0,0);
        $this->my_header_a5($this, 
                'Delivery Order', 
                '');        

        $data = (array) json_decode($r->items);
        foreach ($data as $k => $v)
            $data[$k] = (array) $v;
            
        $iheight = 0.6;
        $base_after_item = 6.7 + (sizeof($data)*$iheight) + 0.1;

        $fy = $this->pdf->GetY();
        $this->pdf->Cell(11.6, 2.6, '', 'LBR', 0, 'L');
        $this->pdf->Cell(8, 2.6, '', 'LBR', 0, 'L');

        $this->pdf->SetY($base_after_item);
        $this->pdf->Cell(6, 3.3, '', 'LBTR', 0, 'L');
        $this->pdf->Cell(13.6, 0.7, '', 'TBR', 0, 'L');
        $this->pdf->SetY($base_after_item+0.7);
        $this->pdf->Cell(6, 0.5, '', '', 0, 'L');
        $this->pdf->Cell(4.5, 2.6, '', 'BR', 0, 'L');
        $this->pdf->Cell(4.5, 2.6, '', 'BR', 0, 'L');
        $this->pdf->Cell(4.6, 2.6, '', 'BR', 0, 'L');

        $rm = $this->pdf->rMargin;
        $yyy = $this->pdf->w - $this->pdf->lMargin - 9.8;
        $this->pdf->SetRightMargin($yyy);
        // $this->pdf->SetRightMargin(11);

        $this->pdf->SetY($fy);
        $this->pdf->SetFont('Arial','', 9);
        $this->pdf->WriteHTML("To : <b>{$r->customer_name}</b> (PIC : <b>{$r->customer_pic}</b>)", 0.5);
        $this->pdf->SetRightMargin($rm);
        $this->pdf->Ln(0.5);
        // $this->pdf->WriteHTML("Alamat : Perumahan Kota Wisata", 0.5);
        // $this->pdf->Ln(0.45);
        // $this->pdf->WriteHTML("Jl. Boulevard Utama Blok I 1/28 (Depan Bank BCA)", 0.5);
        $address2 = ($r->district_name != "" && $r->district_name != null ? $r->district_name . ", " : "") .
                    ($r->city_name != "" && $r->city_name != null ? $r->city_name . ", " : "") .
                    $r->province_name;
        
        $this->pdf->MultiCell(10.6, 0.5, "Alamat : " . $r->customer_address . " - " . $address2);
        
        // if ($address2 != "")
        // {
        //     $this->pdf->WriteHTML(
        //         $address2, 0.5);
        //     $this->pdf->Ln(0.45);
        // }
            

        // PHONES
        $phs = [];
        foreach ($r->customer_phones as $l => $w)
            $phs[] = "+62 ".$w->no;
        $this->pdf->WriteHTML("<b>".join(', ', $phs)."</b>", 0.5);
        // $this->pdf->WriteHTML("Alamat : " . $r->customer_address, 0.5);
        // $this->pdf->Ln(0.45);
        // $this->pdf->WriteHTML("Cibubur, Gunung Putri, Bogor", 0.5);
        // $this->pdf->Ln(0.7);
        // $this->pdf->WriteHTML("<b>+62 898 5945 837</b>", 0.5);

        $this->pdf->SetY($fy);
        $this->pdf->SetX(12.6);
        $this->pdf->Cell(3, 0.5, "No");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(3.9, 0.5, $r->delivery_number, "", 0, "R");
        $this->pdf->Ln(0.5);
        $this->pdf->SetX(12.6);
        $this->pdf->Cell(3, 0.5, "No. PO");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(3.9, 0.5, join(", ", json_decode($r->sales_refs)), "", 0, "R");
        $this->pdf->Ln(0.5);
        $this->pdf->SetX(12.6);
        $this->pdf->Cell(3, 0.5, "KS");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(3.9, 0.5, $r->sales_code, "", 0, "R");
        $this->pdf->Ln(0.5);
        $this->pdf->SetX(12.6);
        $this->pdf->Cell(3, 0.5, "Tgl. Transaksi");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(3.9, 0.5, date('d F Y', strtotime($r->sales_date)), "", 0, "R");
        $this->pdf->Ln(0.5);
        $this->pdf->SetX(12.6);
        $this->pdf->Cell(3, 0.5, "Tgl. Kirim");
        $this->pdf->Cell(0.5, 0.5, ":");
        $this->pdf->Cell(3.9, 0.5, date('d F Y', strtotime($r->delivery_date)), "", 0, "R");
        

        // FOOTER
        $this->pdf->SetY($base_after_item-0.2);
        if ($r->delivery_type_code=='DLV.TYPE.SEND.STAFF')
            $this->pdf->WriteHTML("Pengirim : <b>{$r->transporter_name}</b>");
        if ($r->delivery_type_code=='DLV.TYPE.SEND.DRIVER')
            $this->pdf->WriteHTML("Pengirim : <b>{$r->delivery_send_note}</b>");
        if ($r->delivery_type_code=='DLV.TYPE.PICKUP')
        {
            $this->pdf->Ln(0.3);
            $this->pdf->MultiCell(6, 0.5, "$r->delivery_type_name : {$r->delivery_send_note}", '', 'L');
            $this->pdf->Ln(-0.7);
            // $this->pdf->WriteHTML("<b>{$r->delivery_type_name}</b>");
            // if ($r->delivery_send_note!='')
            // {
            //     $this->pdf->Ln(0.7);
                
            //     // $this->pdf->WriteHTML("<i>Catatan : {$r->delivery_send_note}</i>");
            // }
        }
            
        $this->pdf->Ln(0.7);
        $this->pdf->WriteHTML("Catatan : -");

        $this->pdf->SetY($base_after_item+0.8);
        $this->pdf->SetX(6.7);
        $this->pdf->SetStyle('B', false);
        $this->pdf->Cell(4.5, 0.5, 'Acknowledge,', '', 0, 'C');
        $this->pdf->Cell(4.5, 0.5, 'Admin By,', '', 0, 'C');
        $this->pdf->Cell(4.6, 0.5, 'Customer,', '', 0, 'C');

        $this->pdf->SetY($base_after_item+2.7);
        $this->pdf->SetX(6.7);
        $this->pdf->SetStyle('B', true);
        $this->pdf->Cell(4.5, 0.5, $r->ack_name, '', 0, 'C');
        $this->pdf->Cell(4.5, 0.5, $r->admin_name, '', 0, 'C');
        $this->pdf->Cell(4.6, 0.5, "( .................................. )", '', 0, 'C');

        // DATA
        $this->pdf->SetY(6);
        $this->pdf->Cell(1.5, 0.7, 'NO.', 'LTB', 0, 'C');
        $this->pdf->Cell(12.8, 0.7, 'NAMA ITEM', 'TB', 0, 'L');
        $this->pdf->Cell(2.5, 0.7, 'UNIT', 'TB', 0, 'L');
        $this->pdf->Cell(2.5, 0.7, 'QTY', 'TB', 0, 'R');
        $this->pdf->Cell(0.3, 0.7, '', 'TBR', 0, 'R');
        $this->pdf->Ln(0.7);

        $total = 0;
        $this->pdf->SetStyle('B', false);
        foreach ($data as $k => $v)
        {
            $this->pdf->Cell(1.5, $iheight, $k+1, '', 0, 'C');
            $this->pdf->Cell(12.8, $iheight, $v['item_name'], '', 0, 'L');
            $this->pdf->Cell(2.5, $iheight, $v['item_unit'], '', 0, 'L');
            $this->pdf->Cell(2.5, $iheight, number_format($v['item_qty'], 2), '', 0, 'R');
            $this->pdf->Cell(0.3, $iheight, '', '', 0, 'R');
            $this->pdf->Ln($iheight);

            $total += $v['item_qty'];
        }

        $this->pdf->SetY($base_after_item+0.1);
        $this->pdf->SetX(7);
        $this->pdf->Cell(6, 0.5, 'TOTAL', '', 0, 'L');
        $this->pdf->SetStyle('B', true);
        $this->pdf->Cell(7, 0.5, number_format($total, 2), '', 0, 'R');

        $this->pdf->Output("Delivery_".explode("/", $r->delivery_number)[0]."_".$r->customer_name.".pdf", 'I');
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

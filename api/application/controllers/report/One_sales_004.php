<?php

// Report Fee / Komisi Per Admin
//

class One_sales_004 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'SALES-004';
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_sales_004( $this->sys_input['id'], $this->sys_user['user_id'] );

        $this->pdf->rptclass = $this;
        $this->pdf->rptTitle = '-';
        $this->pdf->setHdrData(["email"=>$r->staff_email, "phones"=>$this->phones($r->staff_phones)]);
        $this->pdf->header_func = "my_header_offer";
        $this->pdf->footer_func = "my_footer_offer";
        // $this->my_watermark($this);

        $this->pdf->SetFont('Arial','', 11);

        $this->pdf->SetMargins(0.7, 0.5, 0.5);
        $this->pdf->AddPage('P', 'A4');

        // $this->pdf->SetLineWidth(0.05);
        // $this->pdf->SetFillColor(192);
        // $this->pdf->RoundedRect(1, 5, 6, 3, 1, 'DF');
        $this->pdf->Rect($this->pdf->lMargin, 4.5, 10, 3.5);
        $this->pdf->Rect($this->pdf->lMargin+11, 5.5, 8, 2.5);

        // phones
        $phones = $this->phones($r->customer_phones);
        $hdr1 = [
            ["To", $r->customer_is_company=="Y"?$r->customer_pic_name:$r->customer_name],
            ["Company", $r->customer_is_company=="Y"?$r->customer_name:"-"],
            ["Mobile", $phones],
            ["Email", $r->customer_email],
        ];
        $this->pdf->SetFont('Arial','', 10);
        $this->pdf->SetY(5);

        foreach ($hdr1 as $k => $v)
        {
            $this->pdf->Cell(0.5, 0.55, '', '', 0, 'L', 0);
            $this->pdf->SetFont('Arial','I', 10);
            $this->pdf->Cell(2.5, 0.55, $v[0], '', 0, 'L', 0);
            $this->pdf->SetFont('Arial','B', 10);
            $this->pdf->Cell(0.2, 0.55, ":", '', 0, 'L', 0);
            // $this->pdf->SetFont('Arial','B', 10);
            $this->pdf->MultiCell(6.5, 0.55, $v[1], '', 'L', 0);
            // $this->pdf->Cell(5, 0.7, ': '.$v[1], '', 0, 'L', 0);
            // $this->pdf->Ln(0.6);
        }

        $this->pdf->SetY(4.5);
        $this->pdf->SetFont('Arial','', 30);
        $this->pdf->Cell(11.5, 0.7, '', '', 0, 'L', 0);
        $this->pdf->Cell(8.5, 0.7, 'Sales Quotation', '', 0, 'L', 0);

        $hdr2 = [
            ["Date", date('d F Y', strtotime($r->offer_date))],
            ["Total Page", "{nb}"],
            ["Quote No", $r->offer_number]
        ];
        $this->pdf->SetFont('Arial','', 10);
        $this->pdf->SetY(5.7);

        foreach ($hdr2 as $k => $v)
        {
            $this->pdf->Cell(11.5, 0.55, '', '', 0, 'L', 0);
            $this->pdf->SetFont('Arial','I', 10);
            $this->pdf->Cell(3, 0.55, $v[0], '', 0, 'L', 0);
            $this->pdf->SetFont('Arial','B', 10);
            $this->pdf->Cell(4, 0.55, ': '.$v[1], '', 0, 'L', 0);
            $this->pdf->Ln(0.55);
        }
        $this->pdf->Ln(1.1);

        $this->pdf->SetFont('Arial','', 10);
        $this->pdf->Cell(19, 0.7, 'Dengan hormat,', '', 0, 'L', 0);
        $this->pdf->Ln(0.7);
        $this->pdf->Cell(19, 0.7, 'Berikut kami informasikan penawaran harga untuk beberapa produk sebagai berikut :', '', 0, 'L', 0);
        $this->pdf->Ln(1.4);

        $this->tableHeader($this->pdf);
        $items = json_decode($r->items);
        $ppns = 0;
        foreach ($items as $k => $v)
        {
            $iheight = 0.7;
            $py = $this->pdf->GetY();
            $this->pdf->Cell(1, 0.7, '', '', 0, 'C', 0);
            $this->pdf->MultiCell(9, 0.7, $v->item_name, 'RB', 'L', 0);
            $iheight = $this->pdf->GetY() - $py;

            $this->pdf->SetY($py);
            $this->pdf->Cell(1, $iheight, $k+1, 'LRB', 0, 'C', 0);
            $this->pdf->Cell(9, $iheight, '', 'RB', 0, 'L', 0);
            $this->pdf->Cell(2.5, $iheight, $v->pack_name, 'RB', 0, 'C', 0);
            $this->pdf->Cell(2.5, $iheight, number_format($v->item_qty, 2) . ' ' . $v->unit_name, 'RB', 0, 'C', 0);
            $this->pdf->Cell(4, $iheight, 'Rp ' . number_format($v->item_price, 2) . ' / ' . $v->unit_name, 'RB', 0, 'C', 0);
            $this->pdf->Ln($iheight);

            if ($v->item_ppn == "Y") $ppns++;
        }

        $this->pdf->Ln(0.7);
        if (sizeof($items) > 7)
            $this->pdf->AddPage('P', 'A4');

        $this->pdf->Cell(19, 0.7, 'Kondisi Penawaran :', '', 0, 'L', 0);

        $cond = [
            ["Franco", $r->offer_franco],
            ["Harga", $ppns>0?($r->offer_ppn=='Y'?"Sudah termasuk PPN {$r->ppn}%":"Belum termasuk PPN {$r->ppn}%"):"Tanpa PPN"],
            ["Pembayaran", $r->term_name],
            ["Delivery Time", $r->offer_delivery],
            ["Stock", $r->offer_stocknote],
            ["Validity", $r->offer_validity]];
        if ($ppns == 0)
            array_splice($cond, 1, 1);
            
        $this->pdf->Ln(1);
        foreach ($cond as $k => $v)
        {
            $this->pdf->Cell(2, 0.7, '', '', 0, 'C', 0);
            $this->pdf->Cell(3, 0.7, $v[0], '', 0, 'L', 0);
            $this->pdf->Cell(9, 0.7, ': '.$v[1], '', 0, 'L', 0);
            $this->pdf->Ln(0.7);
        }

        $this->pdf->Ln(0.3);
        $this->pdf->MultiCell(19, 0.7, 'Demikian informasi yang dapat kami sampaikan, Kami tunggu kelanjutan dari penawaran ini. Atas kerjasamanya kami ucapkan terimakasih.', '', 'L', 0);

        $this->pdf->Ln(0.7);
        $fy = $this->pdf->GetY();

        // $this->pdf->Cell(15, 0.7, '', '', 0, 'C', 1);
        $this->pdf->Cell(4, 0.7, 'Hormat kami,', '', 0, 'C', 1);
        $this->pdf->Ln(2.8);
        $this->pdf->SetFont('Arial','B', 10);
        // $this->pdf->Cell(15, 0.7, '', '', 0, 'C', 1);
        $this->pdf->Cell(4, 0.7, $r->staff_name, 'B', 0, 'C', 0);
        $this->pdf->Ln(0.7);
        $this->pdf->SetFont('Arial','', 10);
        // $this->pdf->Cell(15, 0.7, '', '', 0, 'C', 1);
        $this->pdf->Cell(4, 0.7, 'Marketing', '', 0, 'C', 0);

        $this->pdf->SetY($fy);
        $this->pdf->Cell(9, 0.7, '', '', 0, 'C', 0);
        $this->pdf->Cell(10, 0.7, 'Kami telah menjadi partner bagi berbagai perusahaan,', '', 0, 'C', 1);
        $this->pdf->Ln(0.5);
        $this->pdf->Cell(9, 0.7, '', '', 0, 'C', 0);
        $this->pdf->Cell(10, 0.7, 'silahkan klik link daftar konsumen kami berikut :', '', 0, 'C', 1);
        $this->pdf->Ln(1);
        $this->pdf->Cell(9, 0.7, '', '', 0, 'C', 0);
        $this->pdf->Cell(10, 0.7, 'http://adywater.com/p/daftar-konsumen-kami.html', '', 0, 'C', 1);
        $this->pdf->Ln(1);
        $this->pdf->SetFont('Arial','IB', 10);
        $this->pdf->Cell(9, 0.7, '', '', 0, 'C', 0);
        $this->pdf->Cell(10, 0.7, 'Bank Transfer :', '', 0, 'C', 1);
        $this->pdf->Ln(0.5);
        $this->pdf->Cell(9, 0.7, '', '', 0, 'C', 0);
        $this->pdf->Cell(10, 0.7, 'Rekening Giro Mandiri, 131-00-3331-4446 an CV. Ady Water', '', 0, 'C', 1);
        $this->pdf->Ln(0.5);
        $this->pdf->Cell(9, 0.7, '', '', 0, 'C', 0);
        $this->pdf->Cell(10, 0.7, 'Rekening Giro BCA, 4373-7777-33 an CV. Ady Water', '', 0, 'C', 1);
        // if ($r)
        // {
        //     $data = [];
        //     if (isset($r[1]))
        //         $data = $r[1];
        //     $r = $r[0][0];
        //     $this->pdf->SetMargins(0.7, 0.5, 0.5);
        //     $this->pdf->AddPage('P', 'A4');

        //     $hy = $this->pdf->GetY();
        //     // $this->pdf->Image(base_url() . '/assets/images/logo-1-1.png', 0.8, 0.7, 3);
        //     $this->my_header($this, 
        //         'Laporan Omzet Per Jenjang', 
        //         'Jenjang : '.$r['M_CustomerLevelName'].' | Periode : ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' - ' .  date('d/m/Y', strtotime($this->input->get('edate'))));

            
        //     $this->tableHeader($this->pdf);

        //     $this->pdf->SetFillColor(255,255,255);
        //     $this->pdf->SetTextColor(0,0,0);
        //     $this->pdf->SetFont('Arial','', 9);
        //     foreach ($data as $k => $v)
        //     {
        //         $this->pdf->Cell(1, 0.7, $k+1, 'LB', 0, 'C', 1);
        //         // $this->pdf->Cell(2, 0.7, date('d-m-Y', strtotime($v['so_date'])), 'LB', 0, 'C', 1);
        //         $this->pdf->Cell(9, 0.7, $v['customer_name'].', '.$v['city_name'], 'LB', 0, 'L', 1);
        //         // $this->pdf->Cell(5, 0.7, $v['total'], 'LB', 0, 'L', 1);
        //         $this->pdf->Cell(3, 0.7, number_format($v['total_trx']), 'LB', 0, 'R', 1);
        //         $this->pdf->Cell(3, 0.7, number_format($v['total_qty']), 'LB', 0, 'R', 1);
        //         $this->pdf->Cell(3, 0.7, number_format($v['total']), 'LBR', 0, 'R', 1);
        //         $this->pdf->Ln(0.7);

        //         $grand_total += $v['total'];
        //         $grand_total_qty += $v['total_qty'];
        //         $grand_total_trx += $v['total_trx'];
        //     }

        //     $this->pdf->SetFillColor(222,222,222);
        //     $this->pdf->SetTextColor(0,0,0);
        //     $this->pdf->Cell(10, 0.7, 'GRAND TOTAL', 'LB', 0, 'C', 1);
        //     $this->pdf->Cell(3, 0.7, number_format($grand_total_trx), 'LBR', 0, 'R', 1);
        //     $this->pdf->Cell(3, 0.7, number_format($grand_total_qty), 'LBR', 0, 'R', 1);
        //     $this->pdf->Cell(3, 0.7, number_format($grand_total), 'LBR', 0, 'R', 1);
        // }


        $this->pdf->Output('Penawaran_' . $r->offer_number . '_' . $r->customer_name . '.pdf', 'I');
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

    function tableHeader($me)
    {
        $me->SetFillColor(192,192,192);
        $me->SetTextColor(0,0,0);
        $this->pdf->SetFont('Arial','', 10);

        $me->Cell(1, 0.7, 'NO', 'LTRB', 0, 'C', 1);
        $me->Cell(9, 0.7, 'NAMA PRODUK', 'TRB', 0, 'C', 1);
        $me->Cell(2.5, 0.7, 'KEMASAN', 'TRB', 0, 'C', 1);
        $me->Cell(2.5, 0.7, 'QTY', 'TRB', 0, 'C', 1);
        $me->Cell(4, 0.7, 'HARGA', 'TRB', 0, 'C', 1);
        $me->Ln(0.7);

        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
    }

    function phones ($d)
    {
        $p = json_decode($d);
        $x = [];
        foreach ($p as $k => $v)
        {
            $newNumber = preg_replace('/(^0?|^62?|^\+62)/', '', $v->no);
            $x[] = '+62 '.$newNumber;
        }

        return join(', ', $x);
    }
}
?>

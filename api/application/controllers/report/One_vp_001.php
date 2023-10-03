<?php

class One_vp_001 extends RPT_Controller
{
  var $curPage = 0;
  var $tableTitle = '';
  var $data = false;
  var $inp;
  var $blank;

  function __construct()
  {
    parent::__construct();

    $this->load->library("pdf");
    $this->report_code = 'VP-001';
    $this->blank = false;
  }

  function index()
  {
    $this->pdf = new FPDF("L","cm",array(21,29.7));
    $this->pdf->SetAutoPageBreak(true,1);

    $this->pdf->rptclass = $this;
    $this->pdf->rptTitle = '-';
    // $this->pdf->header_func = "my_header";
    $this->pdf->footer_func = "my_footer";

    $this->pdf->SetFont('Arial','', 11);

    // Get data
    $this->load->model('report/r_report');
    $r = $this->r_report->one_vp_001( date('Y-m-d', strtotime($this->input->get('sdate'))), date('Y-m-d', strtotime($this->input->get('edate'))), $this->input->get('salesid'), $this->input->get('regionid') );

    if ($this->input->get('blank'))
        $this->blank = true;

    if ($r)
    {
        $data = $r;
        $this->pdf->SetMargins(0.7, 0.5, 0.5);
    //   $this->pdf->SetFont("Arial","",10);
        $this->pdf->AddPage('L', 'A4');
        $this->pdf->SetFillColor(255,255,255);
        $this->pdf->SetTextColor(0,0,0);
        $this->pdf->SetFont('Arial','', 9);

        $hy = $this->pdf->GetY();
        $this->my_header($this, 
        'Laporan Piutang Customer Per Sales & Region Detail', 
        'Periode : ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' - ' .  date('d/m/Y', strtotime($this->input->get('edate'))), 'L');

        $currCustomer = 0;
        $total = 0;
        $unpaid = 0;
        $paid = 0;
        $cash = 0;
        $bank = 0;
        $no = 1;

        $gtotal = 0;
        $gpaid = 0;
        $gunpaid = 0;
        $gcash = 0;
        $gbank = 0;

        foreach ($data as $k => $v)
        {
            if ($this->pdf->GetY() > 19)
            { 
                $this->pdf->AddPage('L', 'A4'); 
                $this->curPage = $this->pdf->PageNo(); 
            }

            if ($currCustomer != $v['A_CustomerID'])
            {

                if ($this->curPage != $this->pdf->PageNo())
                    $this->curPage = $this->pdf->PageNo();

                // Print total per Customer
                if ($currCustomer != 0)
                {
                    $this->pdf->SetFont("Arial","B",10);
                    $this->pdf->Cell(6.5, 0.7, 'TOTAL' , 'LBR', 0, 'C', 1);
                    $this->pdf->Cell(2.5, 0.7, number_format($total,0) , 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(2.5, 0.7, number_format($paid,0) , 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(2.5, 0.7, number_format($unpaid,0) , 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(2.5, 0.7, number_format(0,0) , 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(2.5, 0.7, $this->blank?'':number_format($cash,0) , 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(6.5, 0.7, '' , 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(2.5, 0.7, $this->blank?'':number_format($bank, 0) , 'LBR', 0, 'R', 1);
                    // $this->pdf->Cell(7, 0.7, "" , 'LBR', 0, 'R', 1);
                    $this->pdf->Ln(0.7);
                }
                // End of print total per Customer

                $total = 0;
                $unpaid = 0;
                $paid = 0;
                $cash = 0;
                $bank = 0;

                $this->pdf->Ln(0.3);

                if ($this->pdf->GetY() > 17)
                { 
                    $this->pdf->AddPage('L', 'A4'); 
                    $this->curPage = $this->pdf->PageNo(); 
                }

                $currCustomer = $v['A_CustomerID'];
                $this->pdf->SetFont("Arial","B",10);
                $this->pdf->Cell(19, 0.7, $v['A_CustomerName'], '', 0, 'L');
                $this->pdf->Cell(9, 0.7, $v['A_CustomerRegionName'], '', 0, 'R');
                $this->pdf->Ln(0.7);
                // $this->pdf->Cell(1, 0.7, 'A-' , '', 0, 'L');
                $this->tableHeader($this->pdf);
            }
            else
            {
                if ($this->curPage != $this->pdf->PageNo())
                {
                    $this->curPage = $this->pdf->PageNo();
                    $currCustomer = $v['A_CustomerID'];
                    // $this->pdf->Cell(1, 0.7, 'B-' , '', 0, 'L');
                    $this->tableHeader($this->pdf);
                }
            }

            if ($v['paid'] == null)
                $v['paid'] = 0;

            $this->pdf->SetFont("Arial","",10);

            $y_ = $this->pdf->GetY();
            $this->pdf->SetX(12);
            $paids = "";
            $paids2 = "";
            if ($v['paids'] != null && $v['paids'] != "")
            {
                $xpaids = json_decode($v['paids']);
                foreach ($xpaids as $k_ => $v_)
                {
                $paids .= date("d-m-Y", strtotime($v_->date)) . " | {$v_->type}\n";
                $paids2 .= number_format($v_->amount, 0) . "\n";
                $paid += $v_->amount;
                }
            }
          
            // $this->pdf->MultiCell(4.25, 0.7, $paids, "LB", "L");
            // $this->pdf->SetY($y_);
            // $this->pdf->SetX(16.25);
            // $this->pdf->MultiCell(2.75, 0.7, $paids2, "BR", "R");
            // $h_ = ($this->pdf->GetY() - $y_);
            $h_ = 0.7;

            $this->pdf->SetY($y_);
            $this->pdf->SetX(0.7);
            $this->pdf->Cell(2.5, $h_, $v['L_InvoiceNumber'] , 'LBR', 0, 'L');
            $this->pdf->Cell(2, $h_, $v['L_InvoiceDate'] , 'LBR', 0, 'C');
            $this->pdf->Cell(2, $h_, $v['L_InvoiceDueDate'] , 'LBR', 0, 'C');
            $this->pdf->Cell(2.5, $h_, number_format($v['L_InvoiceGrandTotal'],0) , 'LBR', 0, 'R');
            $this->pdf->Cell(2.5, $h_, number_format($v['paid'],0) , 'LBR', 0, 'R');
            $this->pdf->Cell(2.5, $h_, number_format($v['L_InvoiceGrandTotal']-$v['paid'],0) , 'LBR', 0, 'R');
            $this->pdf->Cell(2.5, $h_, '' , 'LBR', 0, 'R');

            if (!$this->blank)
            {
                $this->pdf->Cell(2.5, $h_, number_format($v['cash'],0) , 'LBR', 0, 'R');
                $this->pdf->Cell(2, $h_, $v['transfer'] == 0?($v['bg'] == 0?'':$v['giro_number']):'TR' , 'LBR', 0, 'L');
                $this->pdf->Cell(2, $h_, $v['bank_date'] == null ? '' : date('Y-m-d', strtotime($v['bank_date'])) , 'LBR', 0, 'R');
                $this->pdf->Cell(2.5, $h_, $v['bank_name'] , 'LBR', 0, 'C');
                $this->pdf->Cell(2.5, $h_, number_format($v['transfer'] == 0?$v['bg']:$v['transfer'],0) , 'LBR', 0, 'R');
            }
            else
            {
                $this->pdf->Cell(2.5, $h_, '' , 'LBR', 0, 'R');
                $this->pdf->Cell(2, $h_, '' , 'LBR', 0, 'L');
                $this->pdf->Cell(2, $h_, '' , 'LBR', 0, 'R');
                $this->pdf->Cell(2.5, $h_, '' , 'LBR', 0, 'C');
                $this->pdf->Cell(2.5, $h_, '' , 'LBR', 0, 'R');
            }
            
            // $this->pdf->SetX(19);
            // $this->pdf->Cell(2.5, $h_, number_format($v['L_InvoiceGrandTotal'] - $v['paid'], 0) , 'LBR', 0, 'R');
            // $this->pdf->Cell(7, $h_, "" , 'LBR', 0, 'R');
            $this->pdf->Ln($h_);
            $no++;

            $total += $v['L_InvoiceGrandTotal'];
            $unpaid += $v['L_InvoiceGrandTotal'] - $v['paid'];
            $cash += ($v['cash'] == null ? 0 : $v['cash']);
            $bank += ($v['transfer'] == null ? 0 : $v['transfer']);
            $bank += ($v['bg'] == null ? 0 : $v['bg']);

            $gtotal += $v['L_InvoiceGrandTotal'];
            $gpaid += $v['paid'];
            $gunpaid += $v['L_InvoiceGrandTotal'] - $v['paid'];
            $gcash += ($v['cash'] == null ? 0 : $v['cash']);
            $gbank += ($v['transfer'] == null ? 0 : $v['transfer']);
            $gbank += ($v['bg'] == null ? 0 : $v['bg']);

            // if ($this->pdf->GetY() > 18.5)
            //   $this->pdf->AddPage('L', 'A4');
        }

        $this->pdf->SetFont("Arial","B",10);
        $this->pdf->Cell(6.5, 0.7, 'TOTAL' , 'LBR', 0, 'C', 1);
        $this->pdf->Cell(2.5, 0.7, number_format($total,0) , 'LBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, number_format($paid,0) , 'LBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, number_format($unpaid,0) , 'LBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, number_format(0,0) , 'LBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, $this->blank?'':number_format($cash,0) , 'LBR', 0, 'R', 1);
        $this->pdf->Cell(6.5, 0.7, '' , 'LBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, $this->blank?'':number_format($bank, 0) , 'LBR', 0, 'R', 1);
        // $this->pdf->Cell(7, 0.7, "" , 'LBR', 0, 'R', 1);
        $this->pdf->Ln(1.4);

        $this->pdf->SetFont("Arial","B",10);
        $this->pdf->Cell(6.5, 0.7, 'GRAND TOTAL' , 'TLBR', 0, 'C', 1);
        $this->pdf->Cell(2.5, 0.7, number_format($gtotal,0) , 'TLBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, number_format($gpaid,0) , 'TLBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, number_format($gunpaid,0) , 'TLBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, number_format(0,0) , 'TLBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, $this->blank?'':number_format($gcash,0) , 'TLBR', 0, 'R', 1);
        $this->pdf->Cell(6.5, 0.7, '' , 'TLBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, $this->blank?'':number_format($gbank, 0) , 'TLBR', 0, 'R', 1);
        // $this->pdf->Cell(7, 0.7, "" , 'LBR', 0, 'R', 1);
        $this->pdf->Ln(0.7);
    }

    $this->pdf->Output();
}


//   function v2()
//   {
//     if ($this->data)
//     {
//       $this->pdf->SetFont("Arial","",10);
//       $this->pdf->AddPage('P', 'A4');

//       $currCustomer = 0;
//       $total = 0;
//       $unpaid = 0;
//       if (isset($this->data))
//       {
//         $no = 1;
//         foreach ($this->data as $k => $v)
//         {

//           if ($this->curPage != $this->pdf->PageNo())
//           {
//             $this->curPage = $this->pdf->PageNo();
//             $this->tableHeader2($this->pdf);
//           }

//           if ($v['F_InvoicePaymentDetailAmount'] == null)
//             $v['F_InvoicePaymentDetailAmount'] = 0;

//           $this->pdf->SetFont("Arial","",10);
//           $this->pdf->Cell(7, 0.7, $v['A_CustomerName'] , 'LBR', 0, 'L');
//           $this->pdf->Cell(3, 0.7, $v['L_InvoiceNumber'] , 'LBR', 0, 'C');
//           $this->pdf->Cell(3, 0.7, $v['L_InvoiceDate'] , 'LBR', 0, 'C');
//           $this->pdf->Cell(3, 0.7, $v['L_InvoiceDueDate'] , 'LBR', 0, 'C');
//           $this->pdf->Cell(3, 0.7, number_format($v['L_InvoiceGrandTotal'],0) , 'LBR', 0, 'R');
//           $this->pdf->Cell(3, 0.7, number_format($v['L_InvoiceGrandTotal'] - $v['F_InvoicePaymentDetailAmount'], 0) , 'LBR', 0, 'R');
//           $this->pdf->Ln(0.7);
//           $no++;

//           $total += $v['L_InvoiceGrandTotal'];
//           $unpaid += $v['L_InvoiceGrandTotal'] - $v['F_InvoicePaymentDetailAmount'];
//         }

//         $this->pdf->SetFont("Arial","B",10);
//         $this->pdf->Cell(13, 0.7, 'TOTAL' , 'LBR', 0, 'C', 1);
//         $this->pdf->Cell(3, 0.7, number_format($total,0) , 'LBR', 0, 'R', 1);
//         $this->pdf->Cell(3, 0.7, number_format($unpaid, 0) , 'LBR', 0, 'R', 1);
//       }

//       $this->pdf->Output();
//     }
//   }

  function tableHeader($me)
  {
    $me->SetFont("Arial","B",10);
    $me->SetFillColor(220,220,220);
    // $me->Cell(7, 0.7, 'CUSTOMER' , 'LTBR', 0, 'C');
    $me->Cell(2.5, 1.4, 'INV NUMBER' , 'LTBR', 0, 'C', 1);
    $me->Cell(2, 1.4, 'INV DATE' , 'LTBR', 0, 'C', 1);
    $me->Cell(2, 1.4, 'DUE DATE' , 'TBR', 0, 'C',1);
    $me->Cell(2.5, 1.4, 'TOTAL' , 'TBR', 0, 'C',1);
    $me->Cell(2.5, 1.4, 'PAID' , 'LTBR', 0, 'C', 1);
    $me->Cell(2.5, 1.4, 'UNPAID' , 'LTBR', 0, 'C', 1);
    $me->Cell(2.5, 1.4, 'DISC' , 'LTBR', 0, 'C', 1);
    $me->Cell(2.5, 1.4, 'CASH' , 'LTBR', 0, 'C', 1);
    $me->Cell(9, 0.7, 'BANK' , 'LTBR', 0, 'C', 1);
    $me->Ln(0.7);
    $me->SetX(19.7);
    $me->Cell(2, 0.7, 'NO BG' , 'TBR', 0, 'C',1);
    $me->Cell(2, 0.7, 'TANGGAL' , 'TBR', 0, 'C',1);
    $me->Cell(2.5, 0.7, 'BANK' , 'TBR', 0, 'C',1);
    $me->Cell(2.5, 0.7, 'NOMINAL' , 'TBR', 0, 'C',1);
    // $me->Cell(7, 0.7, 'NOTE' , 'LTBR', 0, 'C', 1);
    $me->Ln(0.7);
  }

  function tableHeader2($me)
  {
    $me->SetFont("Arial","B",10);
    $me->SetFillColor(220,220,220);
    $me->Cell(7, 0.7, 'CUSTOMER' , 'LTBR', 0, 'C',1);
    $me->Cell(3, 0.7, 'INVOICE NO' , 'TBR', 0, 'C',1);
    $me->Cell(3, 0.7, 'DUE DATE' , 'TBR', 0, 'C',1);
    $me->Cell(3, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
    $me->Cell(3, 0.7, 'UNPAID' , 'TBR', 0, 'C',1);
    $me->Ln(0.7);
  }
}
?>

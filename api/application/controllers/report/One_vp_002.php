<?php

class One_vp_002 extends RPT_Controller
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
    $this->report_code = 'VP-002';
    $this->blank = false;
  }

  function index()
  {
    $this->pdf = new FPDF("P","cm",array(21,29.7));
    $this->pdf->SetAutoPageBreak(true,1);

    $this->pdf->rptclass = $this;
    $this->pdf->rptTitle = '-';
    // $this->pdf->header_func = "my_header";
    $this->pdf->footer_func = "my_footer";

    $this->pdf->SetFont('Arial','', 11);

    // Get data
    $this->load->model('report/r_report');
    $r = $this->r_report->one_vp_002( date('Y-m-d', strtotime($this->input->get('sdate'))), date('Y-m-d', strtotime($this->input->get('edate'))), $this->input->get('customerid'), $this->input->get('all') ? $this->input->get('all') : 'N' );

    if ($r)
    {
        $data = $r;
        $this->pdf->SetMargins(0.7, 0.5, 0.5);
    //   $this->pdf->SetFont("Arial","",10);
        $this->pdf->AddPage('P', 'A4');
        $this->pdf->SetFillColor(255,255,255);
        $this->pdf->SetTextColor(0,0,0);
        $this->pdf->SetFont('Arial','', 9);

        $hy = $this->pdf->GetY();
        $this->my_header($this, 
        'Laporan Piutang Customer', 
        'Periode : ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' - ' .  date('d/m/Y', strtotime($this->input->get('edate'))));

        $currCustomer = 0;
        $total = 0;
        $unpaid = 0;
        $paid = 0;
        $cash = 0;
        $bank = 0;
        $no = 1;
        foreach ($data as $k => $v)
        {
            if ($this->pdf->GetY() > 27)
            { 
                $this->pdf->AddPage('P', 'A4'); 
                $this->curPage = $this->pdf->PageNo(); 
            }

            if ($currCustomer != $v['A_CustomerID'])
            {

                if ($this->curPage != $this->pdf->PageNo())
                    $this->curPage = $this->pdf->PageNo();

                // Print total per Customer
                if ($currCustomer != 0)
                {
                    $this->pdf->SetFont("Arial","B",9);
                    $this->pdf->Cell(6.5, 0.7, 'TOTAL' , 'LBR', 0, 'C', 1);
                    $this->pdf->Cell(2.5, 0.7, number_format($total,0) , 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(2.5, 0.7, number_format($paid,0) , 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(2.5, 0.7, number_format($unpaid,0) , 'LBR', 0, 'R', 1);
                    $this->pdf->Cell(5, 0.7, '' , 'LBR', 0, 'R', 1);
                    
                    // $this->pdf->Cell(7, 0.7, "" , 'LBR', 0, 'R', 1);
                    $this->pdf->Ln(0.7);
                }
                // End of print total per Customer

                $total = 0;
                $unpaid = 0;
                $paid = 0;

                $this->pdf->Ln(0.3);

                if ($this->pdf->GetY() > 25)
                { 
                    $this->pdf->AddPage('P', 'A4'); 
                    $this->curPage = $this->pdf->PageNo(); 
                }

                $currCustomer = $v['A_CustomerID'];
                $this->pdf->SetFont("Arial","B",9);
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

            $this->pdf->SetFont("Arial","",9);

            
            $h_ = 0.7;
            $this->pdf->SetX(0.7);
            $this->pdf->Cell(2.5, $h_, $v['L_InvoiceNumber'] , 'LBR', 0, 'L');
            $this->pdf->Cell(2, $h_, $v['L_InvoiceDate'] , 'LBR', 0, 'C');
            $this->pdf->Cell(2, $h_, $v['L_InvoiceDueDate'] , 'LBR', 0, 'C');
            $this->pdf->Cell(2.5, $h_, number_format($v['L_InvoiceGrandTotal'],0) , 'LBR', 0, 'R');
            $this->pdf->Cell(2.5, $h_, number_format($v['paid'],0) , 'LBR', 0, 'R');
            $this->pdf->Cell(2.5, $h_, number_format($v['unpaid'],0) , 'LBR', 0, 'R');
            $this->pdf->Cell(5, $h_, $v['A_CustomerRegionName'] , 'LBR', 0, 'L');

            // if (!$this->blank)
            // {
                // $this->pdf->Cell(2.5, $h_, number_format($v['cash'],0) , 'LBR', 0, 'R');
                // $this->pdf->Cell(2, $h_, $v['transfer'] == 0?($v['bg'] == 0?'':$v['giro_number']):'TR' , 'LBR', 0, 'L');
                // $this->pdf->Cell(2, $h_, $v['bank_date'] == null ? '' : date('Y-m-d', strtotime($v['bank_date'])) , 'LBR', 0, 'R');
                // $this->pdf->Cell(2.5, $h_, $v['bank_name'] , 'LBR', 0, 'C');
                // $this->pdf->Cell(2.5, $h_, number_format($v['transfer'] == 0?$v['bg']:$v['transfer'],0) , 'LBR', 0, 'R');
            // }
            // else
            // {
            //     $this->pdf->Cell(2.5, $h_, '' , 'LBR', 0, 'R');
            //     $this->pdf->Cell(2, $h_, '' , 'LBR', 0, 'L');
            //     $this->pdf->Cell(2, $h_, '' , 'LBR', 0, 'R');
            //     $this->pdf->Cell(2.5, $h_, '' , 'LBR', 0, 'C');
            //     $this->pdf->Cell(2.5, $h_, '' , 'LBR', 0, 'R');
            // }
            
            // $this->pdf->SetX(19);
            // $this->pdf->Cell(2.5, $h_, number_format($v['L_InvoiceGrandTotal'] - $v['paid'], 0) , 'LBR', 0, 'R');
            // $this->pdf->Cell(7, $h_, "" , 'LBR', 0, 'R');
            $this->pdf->Ln($h_);
            $no++;

            $total += $v['L_InvoiceGrandTotal'];
            $unpaid += $v['unpaid'];

            // if ($this->pdf->GetY() > 18.5)
            //   $this->pdf->AddPage('L', 'A4');
        }

        $this->pdf->SetFont("Arial","B",10);
        $this->pdf->Cell(6.5, 0.7, 'TOTAL' , 'LBR', 0, 'C', 1);
        $this->pdf->Cell(2.5, 0.7, number_format($total,0) , 'LBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, number_format($paid,0) , 'LBR', 0, 'R', 1);
        $this->pdf->Cell(2.5, 0.7, number_format($unpaid,0) , 'LBR', 0, 'R', 1);
        $this->pdf->Cell(5, 0.7, '' , 'LBR', 0, 'R', 1);
        // $this->pdf->Cell(7, 0.7, "" , 'LBR', 0, 'R', 1);
        $this->pdf->Ln(0.7);

        // $this->pdf->SetFont("Arial","B",10);
        // $this->pdf->Cell(6.5, 0.7, 'TOTAL' , 'LBR', 0, 'C', 1);
        // $this->pdf->Cell(3, 0.7, number_format($total,0) , 'LBR', 0, 'R', 1);
        // $this->pdf->Cell(7, 0.7, number_format($paid,0) , 'LBR', 0, 'R', 1);
        // $this->pdf->Cell(3, 0.7, number_format($unpaid, 0) , 'LBR', 0, 'R', 1);
        // $this->pdf->Cell(7, 0.7, "" , 'LBR', 0, 'R', 1);
        // $this->pdf->Ln(0.7);
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
    $me->SetFont("Arial","B",9);
    $me->SetFillColor(220,220,220);
    // $me->Cell(7, 0.7, 'CUSTOMER' , 'LTBR', 0, 'C');
    $me->Cell(2.5, 0.7, 'INV NUMBER' , 'LTBR', 0, 'C', 1);
    $me->Cell(2, 0.7, 'INV DATE' , 'LTBR', 0, 'C', 1);
    $me->Cell(2, 0.7, 'DUE DATE' , 'TBR', 0, 'C',1);
    $me->Cell(2.5, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
    $me->Cell(2.5, 0.7, 'PAID' , 'LTBR', 0, 'C', 1);
    $me->Cell(2.5, 0.7, 'UNPAID' , 'LTBR', 0, 'C', 1);
    $me->Cell(5, 0.7, 'REGION' , 'LTBR', 0, 'C', 1);
    $me->Ln(0.7);
  }
}
?>

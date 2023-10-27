<?php

// Report All Stock All Warehouse

class One_fin_021 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'FIN-021';
        $this->wQty = 2;

        $this->load->model("report/r_reportfinance");
    }

    function index() {

        // get data
        $prm = [
            // 'search'=>'%'.$this->sys_input['search'].'%', 
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'ptype'=>isset($this->sys_input['ptype'])?$this->sys_input['ptype']:0
        ];
        $r = $this->r_reportfinance->fin_021($prm);
        
        $this->pdf = new PDF("L","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Laporan Penerimaan Piutang');
        $this->pdf->setRptSubtitle('Periode '.date('d M Y', strtotime($this->sys_input['sdate'])).' s/d '.date('d M Y', strtotime($this->sys_input['edate'])).' ');
        // $this->pdf->setRptSubtitle('Per '.date('d F Y'));
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 9);

        $bill_total = 0;
        $payment_total = 0;

        if ($r)
        {
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('P', 'A4');

            $width = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin;
            foreach ($r as $k => $v)
            {
                $ylimit = $this->pdf->h - 2.9;
                if ($this->pdf->GetY() > $ylimit)
                {
                    $this->tableFooter($this->pdf, ['bill_total' => $bill_total, 
                        'payment_total' => $payment_total]);
                    $this->pdf->AddPage('P', 'A4');
                    // $this->tableHeader($this->pdf, ['staff_name' => '', 'sub_total' => $sub_total, 'bruto_total' => $bruto_total, 'disc_total' => $disc_total]);
                }

                // $this->pdf->SetFillColor(222,222,222);
                // $this->pdf->SetTextColor(0,0,0);
                $this->pdf->Cell(1, 0.7, $k+1, 'LBR', 0, 'L', 0);
                $this->pdf->Cell(2, 0.7, $v['pay_date'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(2.5, 0.7, $v['invoice_number'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell($width-13, 0.7, $v['customer_name'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(2.5, 0.7, number_format($v['invoice_grandtotal']), 'LBR', 0, 'R', 0);
                $this->pdf->Cell(2.5, 0.7, number_format($v['pay_amount']), 'LBR', 0, 'R', 0);
                $this->pdf->Cell(2.5, 0.7, $v['pay_type'], 'LBR', 0, 'L', 0);
                $this->pdf->Ln(0.7);

                $bill_total += $v['invoice_grandtotal'];
                $payment_total += $v['pay_amount'];

                // $invoices = $v['invoices'];
                // foreach ($invoices as $l => $w)
                // {
                //     $this->pdf->Cell(1, 0.7, $l+1, 'LBR', 0, 'L', 0);
                //     $this->pdf->Cell(2, 0.7, $w->invoice_date, 'LBR', 0, 'L', 0);
                //     $this->pdf->Cell(2.5, 0.7, $w->invoice_number, 'LBR', 0, 'L', 0);
                //     $this->pdf->Cell($width-(15.5), 0.7, $w->invoice_note, 'LBR', 0, 'L', 0);
                //     $this->pdf->Cell(2.5, 0.7, $w->term_name, 'LBR', 0, 'L', 0);
                //     $this->pdf->Cell(2.5, 0.7, number_format($w->invoice_grandtotal), 'LBR', 0, 'R', 0);
                //     $this->pdf->Cell(2.5, 0.7, number_format($w->invoice_paid), 'LBR', 0, 'R', 0);
                //     $this->pdf->Cell(2.5, 0.7, number_format($w->invoice_unpaid), 'LBR', 0, 'R', 0);
                //     $this->pdf->Ln(0.7);
                // }
            }

            $this->tableFooter($this->pdf, ['bill_total' => $bill_total, 
                        'payment_total' => $payment_total]);
        }
        
        $this->pdf->Output();
    }

    function tableHeaderCustomer($me, $d)
    {
        $w = $me->w - $me->lMargin - $me->rMargin;
        $this->pdf->SetFont('Arial','', 9);
        
        $me->SetFillColor(222,222,222);
        $me->SetTextColor(0,0,0);
        $me->Ln(0.1);
        $me->Cell($w, 0.7, "" . strtoupper($d['customer_name']), 'LTR', 0, 'L', 1);
        $me->Ln(0.7);
    }

    function tableHeader($me, $d)
    {
        $wItemName = $me->w - $me->lMargin - $me->rMargin - 19;
        $this->pdf->SetFont('Arial','', 9);
        
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(0,0,0);
        $me->Cell($me->w - $me->lMargin - $me->rMargin - 14, 1, "SALES : " . strtoupper($d['staff_name']) , '', 0, 'L', 0);
        $me->Cell(5, 1, "SubTotal : " , '', 0, 'R', 0);
        $me->Cell(3, 0.7, number_format($d['sub_total_ppn']) , '', 0, 'R', 0);
        $me->Cell(3, 0.7, number_format($d['sub_total']+$d['sub_total_ppn']) , '', 0, 'R', 0);
        $me->Cell(3, 0.7, number_format($d['sub_total']) , '', 0, 'R', 0);
        $me->Ln(0.8);

        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->Cell(1, 1, 'NO' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(4, 1, 'NOMOR SO' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'SALES' , 'LTBR', 0, 'C', 1);
        $me->Cell($wItemName, 1, 'NAMA CUSTOMER' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'PPN' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'TOTAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(3, 1, 'TOTAL - PPN' , 'LTBR', 0, 'C', 1);

        // $me->Cell(4, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
        $me->Ln(1);
    }

    function tableFooter($me, $d)
    {
        $wItemName = $me->w - $me->lMargin - $me->rMargin - 19;
        $this->pdf->SetFont('Arial','B', 9);
        
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(0,0,0);
        $me->Cell($me->w - $me->lMargin - $me->rMargin - 10.5, 1, "" , '', 0, 'L', 0);
        $me->Cell(3, 0.7, "TOTAL" , 'LBR', 0, 'C', 0);
        $me->Cell(2.5, 0.7, number_format($d['bill_total']) , 'LBR', 0, 'R', 0);
        $me->Cell(2.5, 0.7, number_format($d['payment_total']) , 'BR', 0, 'R', 0);

        $this->pdf->SetFont('Arial','', 9);
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

    // static function my_header_recapt($me)
    // {
    //     parent::my_header_recapt($me);

    //     $w = $me->pdf->w - $me->pdf->lMargin - $me->pdf->rMargin;
    //     $me->pdf->SetFillColor(172,172,222);
    //     $me->pdf->SetTextColor(0,0,0);
    //     $me->pdf->SetFont('Arial','B', 9);
    //     $me->pdf->Cell(1, 0.7, "NO" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Cell($w - 23.5, 0.7, "NOMOR INVOICE" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Cell(2, 0.7, "TANGGAL" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Cell(2, 0.7, "JT TEMPO" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Cell(1, 0.7, "TERM" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Cell(2.5, 0.7, "SALDO" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Cell(2.5, 0.7, "ON GOING" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Cell(2.5, 0.7, "1 s/d 30 Hari" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Cell(2.5, 0.7, "31 s/d 60 Hari" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Cell(2.5, 0.7, "61 s/d 90 Hari" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Cell(2.5, 0.7, "91 s/d 120 Hari" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Cell(2.5, 0.7, "> 120 Hari" , 'TLBR', 0, 'C', 1);
    //     $me->pdf->Ln(0.8);
    // }

    static function my_header_recapt($me)
    {
        parent::my_header_recapt($me);

        $w = $me->pdf->w - $me->pdf->lMargin - $me->pdf->rMargin;
        $me->pdf->SetFillColor(172,172,222);
        $me->pdf->SetTextColor(0,0,0);
        $me->pdf->SetFont('Arial','B', 9);
        $me->pdf->Cell(1, 0.7, "NO" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell(2, 0.7, "TANGGAL" , 'TBLR', 0, 'C', 1);
        // $me->pdf->Cell($w - 21.5, 0.7, "NOMOR INVOICE" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell(2.5, 0.7, "NOMOR" , 'TBLR', 0, 'C', 1);
        $me->pdf->Cell($w-13, 0.7, "CATATAN" , 'TBLR', 0, 'C', 1);
        
        $me->pdf->Cell(2.5, 0.7, "TAGIHAN" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell(2.5, 0.7, "DIBAYAR" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell(2.5, 0.7, "TIPE BAYAR" , 'TBLR', 0, 'C', 1);
        $me->pdf->Ln(0.8);

        // $me->pdf->Cell($w - 20.5, 0.7, "" , '', 0, 'C', 0);
        // $me->pdf->Cell(2, 0.7, "TEMPO" , 'LBR', 0, 'C', 1);
        // $me->pdf->Cell(1, 0.7, "" , '', 0, 'C', 0);
        // $me->pdf->Cell(2.5, 0.7, "PIUTANG" , 'LBR', 0, 'C', 1);
        // $me->pdf->Cell(2.5, 0.7, "" , '', 0, 'C', 0);
        // $me->pdf->Cell(2.5, 0.7, "1 s/d 30" , 'TLBR', 0, 'C', 1);
        // $me->pdf->Cell(2.5, 0.7, "31 s/d 60" , 'TLBR', 0, 'C', 1);
        // $me->pdf->Cell(2.5, 0.7, "61 s/d 90" , 'TLBR', 0, 'C', 1);
        // $me->pdf->Cell(2.5, 0.7, "91 s/d 120" , 'TLBR', 0, 'C', 1);
        // $me->pdf->Cell(2.5, 0.7, "> 120" , 'TLBR', 0, 'C', 1);
        // $me->pdf->Ln(0.8);
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'staff_id'=>isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0
        ];
        $r = $this->r_reportfinance->fin_007($prm);
        $this->sys_ok($r);
    }

    function excel()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'staff_id'=>isset($this->sys_input['staff_id'])?$this->sys_input['staff_id']:0
        ];
        $r = $this->r_reportfinance->fin_007($prm);

        $this->load->library("Excel");

        // Get data
        $grand_total = 0;
        $sub_total = 0;
        
        $filename = "laporan_piutang_pelanggan_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";
        
        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_fin_007.xls");

            // $data = [];
            $myLine = 7;
            $as = $objPHPExcel->getActiveSheet();
            $g_total = [0, 0, 0];
            foreach ($r as $k => $v)
            {
                $this->copyRowFull($as, $as, 3, $myLine);
                $as->mergeCells("A{$myLine}:E{$myLine}");
                $as->setCellValue("A{$myLine}", $v['customer_name']);
                $myLine++;

                // $total = [0, 0, 0, 0, 0, 0, 0];
                foreach ($v['invoices'] as $l => $w)
                {
                    $no = $l + 1;
                    $mapData = ['A' => $no,
                                'B' => $w->invoice_date,
                                'C' => $w->invoice_number,
                                'D' => $w->sales_name,
                                'E' => $w->invoice_duedate,
                                'F' => $w->term_duration,
                                'G' => $w->invoice_grandtotal,
                                'H' => $w->invoice_paid,
                                'I' => $w->invoice_unpaid,
                                // 'F' => $w->invoice_unpaid,
                                // 'G' => ($w->invoice_diff_date <= 0 ? $w->invoice_unpaid : 0),
                                // 'H' => ($w->invoice_diff_date <= 30 && $w->invoice_diff_date > 0 ? $w->invoice_unpaid : 0),
                                // 'I' => ($w->invoice_diff_date <= 60 && $w->invoice_diff_date > 30  ? $w->invoice_unpaid : 0),
                                // 'J' => ($w->invoice_diff_date <= 90 && $w->invoice_diff_date > 60  ? $w->invoice_unpaid : 0),
                                // 'K' => ($w->invoice_diff_date <= 120 && $w->invoice_diff_date > 90  ? $w->invoice_unpaid : 0),
                                // 'L' => ($w->invoice_diff_date > 120 ? $w->invoice_unpaid : 0),
                            ];
                    foreach ($mapData as $m => $n)
                    {
                        $as->setCellValue($m.$myLine, "{$n}");
                    }

                    $myLine++;
                }

                $this->copyRowFull($as, $as, 4, $myLine);
                $as->mergeCells("A{$myLine}:E{$myLine}");
                $as->setCellValue("A{$myLine}", 'SUB TOTAL');
                $as->setCellValue("G{$myLine}", $v['total_grandtotal']);
                $as->setCellValue("H{$myLine}", $v['total_paid']);
                $as->setCellValue("I{$myLine}", $v['total_unpaid']);

                $range = 'A'.$myLine.':I'.$myLine;
                $BStyle = array(
                    'borders' => array(
                      'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                      ),
                      'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                      )
                    )
                  );
                $as->getStyle($range)
                    ->getFont()->setBold(true);

                // Set top and bottom borders
                $as->getStyle($range)->applyFromArray($BStyle);


                $myLine++;

                
                // $total[0] = $v['total_unpaid'];
                // $total[1] = $v['total_ongoing'];
                // $total[2] = $v['total_30'];
                // $total[3] = $v['total_60'];
                // $total[4] = $v['total_90'];
                // $total[5] = $v['total_120'];
                // $total[6] = $v['total_rest'];
                
                $g_total[0] += $v['total_grandtotal'];
                $g_total[1] += $v['total_paid'];
                $g_total[2] += $v['total_unpaid'];
                // $g_total[3] += $v['total_60'];
                // $g_total[4] += $v['total_90'];
                // $g_total[5] += $v['total_120'];
                // $g_total[6] += $v['total_rest'];

                // $this->copyRowFull($as, $as, 6, $myLine);
                // $mapData = ['F' => $total[0],
                //             'G' => $total[1],
                //             'H' => $total[2],
                //             'I' => $total[3],
                //             'J' => $total[4],
                //             'K' => $total[5],
                //             'L' => $total[6]
                //         ];
                // foreach ($mapData as $m => $n)
                // {
                //     $as->setCellValue($m.$myLine, "{$n}");
                // }
                // $as->getStyle('A'.$myLine)
                //     ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);

                // $as->mergeCells("A{$myLine}:E{$myLine}");
                // $myLine++;
                // $myLine++;
            }

            // GRAND TOTAL
            $myLine++;
            $this->copyRowFull($as, $as, 6, $myLine);
            $as->mergeCells("A{$myLine}:F{$myLine}");
            $mapData = ['G' => $g_total[0],
                            'H' => $g_total[1],
                            'I' => $g_total[2]
                ];
            //                 'I' => $g_total[3],
            //                 'J' => $g_total[4],
            //                 'K' => $g_total[5],
            //                 'L' => $g_total[6]
            //             ];
            foreach ($mapData as $m => $n)
            {
                $as->setCellValue($m.$myLine, "{$n}");
            }
            // $as->getStyle('A'.$myLine)
            //     ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);

            $as->removeRow(3,4);
            $as->setCellvalue("J2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("J3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");
            
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Cache-Control: max-age=0");
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            ob_clean();
            $objWriter->save("php://output");

        }   
        $this->sys_ok(["report_url"=>$this->REPORT_EXCEL_URL.$filename]);
    }

    function copyRowFull(&$ws_from, &$ws_to, $row_from, $row_to) {
        $ws_to->getRowDimension($row_to)->setRowHeight($ws_from->getRowDimension($row_from)->getRowHeight());
        $lastColumn = $ws_from->getHighestColumn();
        ++$lastColumn;
        for ($c = 'A'; $c != $lastColumn; ++$c) {
          $cell_from = $ws_from->getCell($c.$row_from);
          $cell_to = $ws_to->getCell($c.$row_to);
          $cell_to->setXfIndex($cell_from->getXfIndex()); // black magic here
          $cell_to->setValue($cell_from->getValue());
        }
    }
}
?>

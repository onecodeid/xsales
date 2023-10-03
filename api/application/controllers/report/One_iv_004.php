<?php

// Report Mutasi Barang per Gudang
class One_iv_004 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'IV-004';
        $this->wQty = 2;

        $this->load->model("report/r_reportinventory");
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->load->model('master/m_warehouse');
        $wh = $this->m_warehouse->get($this->sys_input['warehouseid']);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Laporan Mutasi Barang');
        $this->pdf->setRptSubtitle('Gudang '.$wh->warehouse_name);
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_iv_004( $this->sys_input['warehouseid'],
                                            date('Y-m-d', strtotime($this->sys_input['sdate'])),
                                            date('Y-m-d', strtotime($this->sys_input['edate'])) );

        //
        $grand_total = 0;
        $sub_total = 0;
        

        if ($r)
        {            
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');

            $w = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin;

            $this->tableHeader($this->pdf);
            $this->pdf->SetFont('Arial','', 8);

            $d = $r;
            foreach ($d as $k => $v)
            {
                $ylimit = $this->pdf->h - 2.2;
                if ($this->pdf->GetY() > $ylimit)
                {
                    $this->pdf->AddPage('L', 'A4');
                    $this->tableHeader($this->pdf);

                    $sub_total = 0;
                }

                $v['log_desc'] = preg_replace("/(\[customer\])/", $v['customer_name'], $v['log_desc']);
                $v['log_desc'] = preg_replace("/(\[supplier\])/", $v['vendor_name'], $v['log_desc']);
                $v['log_desc'] = preg_replace("/(\[towarehouse\])/", $v['to_warehouse_name'], $v['log_desc']);
                $v['log_date'] = date("d-m-Y", strtotime($v['log_date']));

                $this->pdf->Cell(2, 0.7, $v['log_date'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(3.5, 0.7, $v['log_ref'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(2.5, 0.7, $v['log_item_code'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(($w-12)/2, 0.7, $v['log_item'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(($w-12)/2, 0.7, $v['log_desc'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(2, 0.7, number_format($v['log_in']), 'LBR', 0, 'R', 0);
                $this->pdf->Cell(2, 0.7, number_format($v['log_out']), 'LBR', 0, 'R', 0);
                
                $this->pdf->Ln(0.7);
            }

            // $this->pdf->Ln(0.2);
            // $this->pdf->SetFont('Arial','B', 9);
            // $this->pdf->Cell($wItemName+3, 0.7, 'TOTAL', 'BLTR', 0, 'C', 0);
            // $this->pdf->Cell($wQty, 0.7, '', 'BTR', 0, 'R', 0);
            // $this->pdf->Cell(2, 0.7, '', 'BTR', 0, 'C', 0);
            // $this->pdf->Cell($wQty, 0.7, '', 'BTR', 0, 'R', 0);
            // $this->pdf->Cell($wQty+1, 0.7, number_format($grand_total, 2), 'BTR', 0, 'R', 0);




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

    function tableHeader($me)
    {
        $w = $me->w - $me->lMargin - $me->rMargin;
        $this->pdf->SetFont('Arial','', 8);
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(3.5, 1, 'REF' , 'LTBR', 0, 'C', 1);
        $me->Cell(2.5, 1, 'KODE ITEM' , 'LTBR', 0, 'C', 1);
        $me->Cell(($w-12)/2, 1, 'NAMA ITEM' , 'LTBR', 0, 'C', 1);
        $me->Cell(($w-12)/2, 1, 'KETERANGAN' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'MASUK' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'KELUAR' , 'LTBR', 0, 'C', 1);

        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
        $me->Ln(1);
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'warehouse_id'=>isset($this->sys_input['warehouse_id'])?$this->sys_input['warehouse_id']:0
        ];
        $r = $this->r_reportinventory->iv_004($prm);

        foreach ($r as $k => $v)
        { 
            $v['log_desc'] = preg_replace("/(\[customer\])/", $v['customer_name'], $v['log_desc']);
            $v['log_desc'] = preg_replace("/(\[supplier\])/", $v['vendor_name'], $v['log_desc']);
            $v['log_desc'] = preg_replace("/(\[towarehouse\])/", $v['to_warehouse_name'], $v['log_desc']);
            $v['log_date'] = date("d-m-Y", strtotime($v['log_date']));
            $r[$k] = $v;
        }
        $this->sys_ok($r);
    }

    function excel()
    { 
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'warehouse_id'=>isset($this->sys_input['warehouse_id'])?$this->sys_input['warehouse_id']:0
        ];
        $r = $this->r_reportinventory->iv_004($prm);

        $this->load->library("Excel");
        $grand_total = 0;

        //WAREHOUSE
        $warehouse_name = 'SEMUA GUDANG';
        $this->load->model("master/m_warehouse");
        $warehouse = $this->m_warehouse->get($this->sys_input['warehouse_id']);
        if ($warehouse) $warehouse_name = $warehouse->warehouse_name;
        
        $filename = "laporan_mutasi_barang_per_gudang_".date('d.m.Y', strtotime($this->input->get('sdate'))).".xls";

        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_iv_004.xls");

            // $data = [];
            $myLine = 3;
            $as = $objPHPExcel->getActiveSheet();
            
            // $g_total = [0, 0, 0, 0, 0, 0, 0];
            foreach ($r as $k => $v)
            {
                $v['log_desc'] = preg_replace("/(\[customer\])/", $v['customer_name'], $v['log_desc']);
                $v['log_desc'] = preg_replace("/(\[supplier\])/", $v['vendor_name'], $v['log_desc']);
                $v['log_desc'] = preg_replace("/(\[towarehouse\])/", $v['to_warehouse_name'], $v['log_desc']);
                $v['log_date'] = date("d-m-Y", strtotime($v['log_date']));
                
                $mapData = ['A' => $k+1,
                                'B' => $v['log_date'], //$v['item_code'],
                                'C' => $v['log_ref'], //$v['item_name'],
                                'D' => $v['log_item_code'], //$v['category_name'],
                                'E' => $v['log_item'], //$v['stock_qty'],
                                'F' => $v['log_desc'],
                                'G' => $v['log_in'],
                                'H' => $v['log_out'],
                                'I' => $v['unit_name']
                            ];
                foreach ($mapData as $m => $n) { $as->setCellValue($m.$myLine, "{$n}"); }
                $myLine++;

                // $grand_total += $v['stock_qty'] * $v['item_hpp'];

                // foreach ($v['details'] as $l => $w)
                // {
                //     $no = $l + 1;
                //     $mapData = ['A' => $no,
                //                 'B' => $w->journal_date,
                //                 'C' => $w->journal_number,
                //                 'D' => $w->journal_type_name,
                //                 'E' => $w->ledger_note,
                //                 'F' => $w->journal_debit,
                //                 'G' => $w->journal_credit,
                //                 'H' => $w->balance
                //             ];
                //     foreach ($mapData as $m => $n)
                //     {
                //         $as->setCellValue($m.$myLine, "{$n}");
                //     }
                //     $grand_total['debit'] += $w->journal_debit;
                //     $grand_total['credit'] += $w->journal_credit;

                //     $myLine++;
                // }
                
                // $this->copyRowFull($as, $as, 5, $myLine);
                // $mapData = ['H' => $v["balance_close"],
                //             'F' => $v["total_debit"],
                //             'G' => $v["total_credit"],
                //         ];
                // foreach ($mapData as $m => $n)
                // {
                //     $as->setCellValue($m.$myLine, "{$n}");
                // }
                // $as->setCellValue("C{$myLine}", "JUMLAH");

                // $myLine++;
            }

            // GRAND TOTAL
            // $this->copyRowFull($as, $as, 4, $myLine);
            // $mapData = ['H' => $grand_total
            //         ];
            // foreach ($mapData as $m => $n)
            // {
            //     $as->setCellValue($m.$myLine, "{$n}");
            // }
            // $as->setCellValue("C{$myLine}", "GRAND TOTAL");


            // GRAND TOTAL
            // $this->copyRowFull($as, $as, 6, $myLine);
            // $as->mergeCells("A{$myLine}:C{$myLine}");
            // $mapData = ['D' => $g_total[0],
            //                 'E' => $g_total[1],
            //                 'F' => $g_total[2],
            //                 'G' => $g_total[3],
            //                 'H' => $g_total[4]
            //             ];
            // foreach ($mapData as $m => $n)
            // {
            //     $as->setCellValue($m.$myLine, "{$n}");
            // }
            // $as->getStyle('A'.$myLine)
            //     ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);

            // $as->removeRow(3,2);
            $as->setCellvalue("J2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("J3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");
            $as->setCellvalue("F1", $warehouse_name);
            // $as->setCellvalue("I3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");

            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Cache-Control: max-age=0");
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            ob_clean();
            $objWriter->save("php://output");

        }   
        // $this->sys_ok(["report_url"=>$this->REPORT_EXCEL_URL.$filename]);
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

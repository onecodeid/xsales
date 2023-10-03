<?php

// Report All Stock All Warehouse

class One_iv_003 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'IV-003';
        $this->wQty = 2;

        $this->load->model("report/r_reportinventory");
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $date = isset($this->sys_input['date'])?date('Y-m-d', strtotime($this->sys_input['date'])):date('Y-m-d');
        $this->load->model('master/m_warehouse');
        $wh = $this->m_warehouse->get($this->sys_input['warehouseid']);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Laporan Ketersediaan Barang');
        $this->pdf->setRptSubtitle('Gudang '.$wh->warehouse_name.' Periode '.date('d/m/Y', strtotime($date)));
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_iv_003( $date, $this->sys_input['warehouseid'] );

        $warehouse = 
        $grand_total = 0;
        $sub_total = 0;

        if ($r)
        {
            $d = [];
            $items = [];
            foreach ($r[0] as $k => $v)
            {
                $items['I'.$v['item_id']] = $v;
                if (!isset($d['I'.$v['item_id']]))
                    $d['I'.$v['item_id']] = [];
                $d['I'.$v['item_id']][$v['warehouse_code']] = ['qty'=>$v['stock_qty'],'hpp'=>$v['item_hpp']];
            }

            
            // $data = isset($r[1])?$r[1]:[];
            // $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('P', 'A4');

            $this->tableHeader($this->pdf, $r[1]);

            $this->pdf->SetFont('Arial','', 9);

            $wQty = $this->wQty;
            $wItemName = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin - 10 - ($wQty*sizeof($r[1]));

            foreach ($d as $k => $v)
            {
                $ylimit = $this->pdf->h - 2.2;
                if ($this->pdf->GetY() > $ylimit)
                {
                    $this->pdf->AddPage('P', 'A4');
                    $this->tableHeader($this->pdf, $r[1]);

                    $sub_total = 0;
                }

                $this->pdf->Cell(3, 0.7, $items[$k]['item_code'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell($wItemName, 0.7, $items[$k]['item_name'], 'BR', 0, 'L', 0);
                foreach ($r[1] as $l => $w)
                {
                    if (isset($v[$w['warehouse_code']]['qty']))
                    {
                        $this->pdf->Cell($wQty, 0.7, number_format($v[$w['warehouse_code']]['qty'], 2), 'BR', 0, 'R', 0);
                        $this->pdf->Cell(2, 0.7, $items[$k]['unit_name'], 'BR', 0, 'C', 0);
                        $this->pdf->Cell($wQty, 0.7, number_format($v[$w['warehouse_code']]['hpp'], 2), 'BR', 0, 'R', 0);
                        $this->pdf->Cell($wQty+1, 0.7, number_format($v[$w['warehouse_code']]['qty'] * $v[$w['warehouse_code']]['hpp'], 2), 'BR', 0, 'R', 0);

                        $grand_total += $v[$w['warehouse_code']]['qty'] * $v[$w['warehouse_code']]['hpp'];
                        $sub_total += $v[$w['warehouse_code']]['qty'] * $v[$w['warehouse_code']]['hpp'];
                    }
                    else
                    {
                        $this->pdf->Cell($wQty, 0.7, 0, 'BR', 0, 'R', 0);
                        $this->pdf->Cell(2, 0.7, $items[$k]['unit_name'], 'BR', 0, 'C', 0);
                        $this->pdf->Cell($wQty, 0.7, 0, 'BR', 0, 'R', 0);
                        $this->pdf->Cell($wQty+1, 0.7, 0, 'BR', 0, 'R', 0);
                    }   
                }

                $this->pdf->Ln(0.7);
            }

            $this->pdf->Ln(0.2);
            $this->pdf->SetFont('Arial','B', 9);
            $this->pdf->Cell($wItemName+3, 0.7, 'TOTAL', 'BLTR', 0, 'C', 0);
            $this->pdf->Cell($wQty, 0.7, '', 'BTR', 0, 'R', 0);
            $this->pdf->Cell(2, 0.7, '', 'BTR', 0, 'C', 0);
            $this->pdf->Cell($wQty, 0.7, '', 'BTR', 0, 'R', 0);
            $this->pdf->Cell($wQty+1, 0.7, number_format($grand_total, 2), 'BTR', 0, 'R', 0);
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
        $wItemName = $me->w - $me->lMargin - $me->rMargin - 10 - ($wQty*sizeof($d));
        $this->pdf->SetFont('Arial','', 9);
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->Cell(3, 1, 'KODE' , 'LTBR', 0, 'C', 1);
        $me->Cell($wItemName, 1, 'NAMA ITEM' , 'LTBR', 0, 'C', 1);
        foreach ($d as $k => $v)
            $me->Cell($wQty, 1, 'QTY'/*$v['warehouse_short_name']*/ , 'LTBR', 0, 'R', 1);
        $me->Cell(2, 1, 'UNIT' , 'LTBR', 0, 'C', 1);
        $me->Cell(2, 1, 'HPP' , 'LTBR', 0, 'R', 1);
        $me->Cell(3, 1, 'NOMINAL' , 'LTBR', 0, 'R', 1);

        // $me->Cell(4, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
        $me->Ln(1);
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            // 'page'=>$this->sys_input['page'],
            'sdate'=>$this->sys_input['sdate'],
            // 'edate'=>$this->sys_input['edate'],
            'warehouse_id'=>isset($this->sys_input['warehouse_id'])?$this->sys_input['warehouse_id']:0,
            // 'account_ids'=>isset($this->sys_input['account_ids'])?$this->sys_input['account_ids']:''
        ];
        $r = $this->r_reportinventory->iv_003($prm);
        $this->sys_ok($r);
    }

    function excel()
    { 
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            // 'page'=>1, 'limit'=>99999,
            'sdate'=>$this->sys_input['sdate'],
            // 'edate'=>$this->sys_input['edate'],
            'warehouse_id'=>isset($this->sys_input['warehouse_id'])?$this->sys_input['warehouse_id']:0,
            // 'account_ids'=>isset($this->sys_input['account_ids'])?$this->sys_input['account_ids']:''
        ];
        $r = $this->r_reportinventory->iv_003($prm);
        $this->load->library("Excel");

        // Get data
        // $this->load->model('report/r_report');

        // $sdate = date('Y-m-d');
        // $edate = date('Y-m-d');
        // $levelid = 0;
        // if (isset($this->sys_input['sdate'])) $sdate = $this->sys_input['sdate'];
        // if (isset($this->sys_input['edate'])) $edate = $this->sys_input['edate'];

        // $r = $this->r_report->one_fin_002( $sdate, $edate );
        //
        $grand_total = 0;

        //WAREHOUSE
        $warehouse_name = 'SEMUA GUDANG';
        $this->load->model("master/m_warehouse");
        $warehouse = $this->m_warehouse->get($this->sys_input['warehouse_id']);
        if ($warehouse) $warehouse_name = $warehouse->warehouse_name;
        
        $filename = "laporan_ketersediaan_barang_per_gudang_".date('d.m.Y', strtotime($this->input->get('sdate'))).".xls";

        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_iv_003.xls");

            // $data = [];
            $myLine = 5;
            $as = $objPHPExcel->getActiveSheet();
            
            // $g_total = [0, 0, 0, 0, 0, 0, 0];
            foreach ($r as $k => $v)
            {
                // $this->copyRowFull($as, $as, 3, $myLine);
                // $as->mergeCells("A{$myLine}:B{$myLine}");
                // $as->mergeCells("C{$myLine}:E{$myLine}");

                // $as->setCellValue("F{$myLine}", "DEBIT");
                // $as->setCellValue("G{$myLine}", "KREDIT");
                // $as->setCellValue("H{$myLine}", "SALDO");

                // $as->setCellValue("A{$myLine}", $v['group_name']);
                // $as->setCellValue("C{$myLine}", $v['account_name']);
                // // $as->setCellValue("H{$myLine}", $v['balance_open']);
                // $as->getStyle('C'.$myLine)
                //     ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);
                // $myLine++;

                // SALDO AWAL
                $mapData = ['A' => $k+1,
                                'B' => $v['item_code'],
                                'C' => $v['item_name'],
                                'D' => $v['category_name'],
                                'E' => $v['stock_qty'],
                                'F' => $v['unit_name'],
                                'G' => $v['item_hpp'],
                                'H' => $v['stock_qty'] * $v['item_hpp']
                            ];
                foreach ($mapData as $m => $n) { $as->setCellValue($m.$myLine, "{$n}"); }
                $myLine++;

                $grand_total += $v['stock_qty'] * $v['item_hpp'];

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
            $this->copyRowFull($as, $as, 4, $myLine);
            $mapData = ['H' => $grand_total
                    ];
            foreach ($mapData as $m => $n)
            {
                $as->setCellValue($m.$myLine, "{$n}");
            }
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

            $as->removeRow(3,2);
            $as->setCellvalue("I2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("D1", $warehouse_name);
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

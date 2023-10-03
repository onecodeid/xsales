<?php

// Report All Stock All Warehouse

class One_iv_007 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    var $gsub_total = 0;
    var $gsub_total_ppn = 0;
    var $gsub_total_qty = 0;
    var $gunit = "";
    var $gitem = "";

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'IV-007';

        $this->load->model("report/r_reportinventory");
    }

    function index() {
        // $this->pdf = new PDF("P","cm",array(21,29.7));
        // $this->pdf->SetAutoPageBreak(true,1);

        // $this->pdf->rptclass = $this;
        // $this->pdf->setRptTitle('Laporan Pergerakan Barang per Gudang');
        // $this->pdf->setRptSubtitle('Periode '.date('d M Y', strtotime($this->sys_input['sdate'])).' s/d '.date('d M Y', strtotime($this->sys_input['edate'])).' ');
        // $this->pdf->header_func = "my_header_recapt";
        // $this->pdf->footer_func = "my_footer";
       
        // $this->pdf->Output();
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'category_id'=>isset($this->sys_input['category_id'])?$this->sys_input['category_id']:0,
            'warehouse_id'=>isset($this->sys_input['warehouse_id'])?$this->sys_input['warehouse_id']:0
        ];
        $r = $this->r_reportinventory->iv_007($prm);
        $this->sys_ok($r);
    }

    function excel()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>1, 'limit'=>99999,
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'category_id'=>isset($this->sys_input['category_id'])?$this->sys_input['category_id']:0,
            'warehouse_id'=>isset($this->sys_input['warehouse_id'])?$this->sys_input['warehouse_id']:0
        ];
        $r = $this->r_reportinventory->iv_007($prm);

        $this->load->library("Excel");

        $grand_total = 0;
        $sub_total = 0;
        
        $filename = "laporan_rincian_persediaan_barang_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";
        
        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_iv_007.xls");

            // $data = [];
            $myLine = 6;
            $as = $objPHPExcel->getActiveSheet();
            $cur_cat = 0;
            $g_total = [0, 0, 0, 0];

            // KATEGORI
            if ($prm['category_id'] != 0 && sizeof($r['records'])>0)
                $as->setCellValue('G1', $r['records'][0]['category_name']);
            foreach ($r['records'] as $k => $v)
            {

                
                // if ($cur_cat != $v['category_id'])
                // {
                //     if ($k > 0)
                //     {
                //         $this->copyRowFull($as, $as, 6, $myLine);
                //         $as->mergeCells("A{$myLine}:I{$myLine}");
                //         $as->setCellValue('A'.$myLine, 'TOTAL KATEGORI ' . strtoupper($r['records'][$k-1]['category_name']));
                //         $as->setCellValue('J'.$myLine, $total_cat[0]);
                //         $as->setCellValue('K'.$myLine, $total_cat[1]);
                //         $as->setCellValue('L'.$myLine, $total_cat[2]);
                //         $as->setCellValue('M'.$myLine, $total_cat[3]);
                //         $myLine++; $myLine++;
                //     }

                //     $cur_cat = $v['category_id'];
                //     $total_cat = [0, 0, 0, 0];
                // }

                $this->copyRowFull($as, $as, 3, $myLine);
                $as->mergeCells("A{$myLine}:B{$myLine}");
                $as->mergeCells("C{$myLine}:I{$myLine}");
                // $as->setCellValue("F{$myLine}", 'SALDO AWAL');
                $as->setCellValue("A{$myLine}", $v['category_name']);
                $as->setCellValue("C{$myLine}", $v['item_name']);

                // Get the cell object
                $cell = $as->getCell("C{$myLine}");
                $style = $cell->getStyle();
                $alignment = $style->getAlignment();
                $alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $myLine++;

                // // $total = [0, 0, 0, 0, 0, 0, 0];
                $as->setCellValue('E'.$myLine, "SALDO AWAL");
                $as->setCellValue('H'.$myLine, isset($v['details'][0])?$v['details'][0]->log_before_qty:0);
                $myLine++;
                foreach ($v['details'] as $l => $w)
                {
                    $no = $l + 1;
                    $mapData = ['A' => $no,
                                'B' => $w->log_date,
                                'C' => $w->log_ref_number,
                                'D' => $w->log_type_name,
                                'E' => $w->log_type_text,
                                'F' => $w->warehouse_name,
                                'G' => $v['unit_name'],
                                'H' => $w->log_qty,
                                'I' => $w->log_after_qty,
                                // 'H' => $w->stock_qty < 0 ? $w->stock_qty : 0,
                                // 'I' => $w->stock_after_qty
                                // 'I' => $w->invoice_unpaid == 0 ? 'Lunas' : ($w->invoice_paid > 0 ? 'Sebagian' : 'Baru')
                            ];
                    foreach ($mapData as $m => $n)
                    {
                        $as->setCellValue($m.$myLine, "{$n}");
                    }
                // //     $total[0] += $w->detail_qty;
                // //     $total[1] += 0;
                // //     $total[2] += 0;
                // //     $total[3] += $w->detail_subtotal;
                // //     $total[4] += $w->detail_disctotal;
                // //     $total[5] += $w->detail_ppnamount;
                // //     $total[6] += $w->detail_total;

                // //     $total_cat[0] += $w->detail_subtotal;
                // //     $total_cat[1] += $w->detail_disctotal;
                // //     $total_cat[2] += $w->detail_ppnamount;
                // //     $total_cat[3] += $w->detail_total;

                // //     $g_total[0] += $w->detail_subtotal;
                // //     $g_total[1] += $w->detail_disctotal;
                // //     $g_total[2] += $w->detail_ppnamount;
                // //     $g_total[3] += $w->detail_total;

                    $myLine++;
                }
                $as->setCellValue('E'.$myLine, "SALDO AKHIR");
                $as->setCellValue('I'.$myLine, sizeof($v['details'])>0?$v['details'][sizeof($v['details'])-1]->log_after_qty:0);

                // set bold
                // Get the cell object
                // $as->getCell('E'.$myLine.':H'.$myLine);
                $style = $as->getStyle('E'.$myLine.':I'.$myLine);
                $font = $style->getFont();
                $font->setBold(true);

                $myLine+=2;
                
                // $this->copyRowFull($as, $as, 4, $myLine);
                // ->setCellValue('I1', 'Periode ' . date('d/m/Y', strtotime($sdate)) . ' - ' . date('d/m/Y', strtotime($edate)));
                
                // $this->copyRowFull($as, $as, 5, $myLine);
                // $mapData = ['G' => $total[0],
                //             'H' => $total[1],
                //             'I' => $total[2],
                //             'J' => $total[3],
                //             'K' => $total[4],
                //             'L' => $total[5],
                //             'M' => $total[6]
                //         ];
                // foreach ($mapData as $m => $n)
                // {
                //     $as->setCellValue($m.$myLine, "{$n}");
                // }

                // $as->mergeCells("A{$myLine}:F{$myLine}");
                // $as->setCellValue('A'.$myLine, 'SALDO AWAL & AKHIR');
                // $as->setCellValue('F'.$myLine, $v['logs'][0]->stock_before_qty);
                // $as->setCellValue('G'.$myLine, $v['stock_in_qty']);
                // $as->setCellValue('H'.$myLine, $v['stock_out_qty']);
                // $as->setCellValue('I'.$myLine, $v['logs'][sizeof($v['logs'])-1]->stock_after_qty);
                // $as->mergeCells("A{$myLine}:D{$myLine}");
                // $as->getStyle('A'.$myLine.':I'.$myLine)
                //     ->applyFromArray([
                //         'borders' => array(
                //             'bottom' => array(
                //                 'style' => PHPExcel_Style_Border::BORDER_DOUBLE
                //             ),
                //             'top' => [ 'style' => PHPExcel_Style_Border::BORDER_THIN ]
                //         )
                //     ]);
                // $as->getStyle('A'.$myLine.':I'.$myLine)->getFont()->setBold( true );
                // $as->getStyle('A'.$myLine)
                // ->applyFromArray([
                //     'alignment' => array(
                //         'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                //     )
                // ]);
                //     $myLine++;
                //     $myLine++;

                // $myLine++;

                
            //     $cl_name = '';
            //     if ($v['M_CustomerLevelCode'] == 'CUST.DISTRIBUTOR') $cl_name = 'Dist';
            //     if ($v['M_CustomerLevelCode'] == 'CUST.AGENCY') $cl_name = 'Agen';
            //     if ($v['M_CustomerLevelCode'] == 'CUST.RESELLER') $cl_name = 'Resl';
            //     if ($v['M_CustomerLevelCode'] == 'CUST.ENDUSER') $cl_name = 'User';

            //     $data[] = [$k+1, 
            //             date('Y-m-d', strtotime($v['F_FeeL_SoDate'])),
            //             $v['L_SoNumber'],
            //             $v['S_UserProfileFullName'],
            //             $v['M_CustomerName'] . ' / ' . $cl_name,
            //             $v['M_ItemName'] != null ? $v['M_ItemName'] : $v['M_PacketName'],
            //             $v['F_FeeAmount'],
            //             $v['F_FeeQty'],
            //             $v['F_FeeTotal']];
            }

            // TOTAL KATEGORI
            // $this->copyRowFull($as, $as, 6, $myLine);
            // $as->mergeCells("A{$myLine}:I{$myLine}");
            // $as->setCellValue('A'.$myLine, 'TOTAL KATEGORI ' . $r['records'][sizeof($r['records']) - 1]['category_name']);
            // $as->setCellValue('J'.$myLine, $total_cat[0]);
            // $as->setCellValue('K'.$myLine, $total_cat[1]);
            // $as->setCellValue('L'.$myLine, $total_cat[2]);
            // $as->setCellValue('M'.$myLine, $total_cat[3]);
            // $myLine++; $myLine++;

            // GRAND TOTAL
            // $this->copyRowFull($as, $as, 7, $myLine);
            // $as->mergeCells("A{$myLine}:I{$myLine}");
            // $as->setCellValue('A'.$myLine, 'GRAND TOTAL');
            // $as->setCellValue('J'.$myLine, $g_total[0]);
            // $as->setCellValue('K'.$myLine, $g_total[1]);
            // $as->setCellValue('L'.$myLine, $g_total[2]);
            // $as->setCellValue('M'.$myLine, $g_total[3]);

            $as->removeRow(3,3);
            $as->setCellvalue("J2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("J3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");
            // 
            // $baseRow = 'A5';
            // 
            // $as->fromArray($data, null, $baseRow);

            // $as->setCellValue('A2', 'Periode : ' . date('d/m/Y', strtotime($this->input->get('sdate'))) . ' - ' .  date('d/m/Y', strtotime($this->input->get('edate'))));
            

            //     ->setCellValue('I1', 'Periode ' . date('d/m/Y', strtotime($sdate)) . ' - ' . date('d/m/Y', strtotime($edate)));


            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Cache-Control: max-age=0");
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            ob_clean();
            $objWriter->save("php://output");
            // $objWriter->save("/Users/Shared/tmp/".$filename);
            // $objWriter->save("/home/one/Web/uploads/reports/".$filename);
            // $objWriter->save(str_replace('one-sales/api/application/controllers/report/', 'uploads/reports/', str_replace('.php', '.xls', __FILE__)));
            
            // 
            // echo json_encode(["status"=>"OK", 
            //     "data"=>"http://{$_SERVER['SERVER_NAME']}/pungkook/api/excel/".str_replace('.php', '.xls', pathinfo(__FILE__, PATHINFO_BASENAME))]);

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

<?php

// Report Fee / Komisi Per Admin
//

class One_fin_002_excel extends RPT_Controller
{
    var $report_code;
    // var $pdf;

    function __construct()
    {
        parent::__construct();

        $this->load->library("Excel");
        $this->report_code = 'FIN-001';
    }

    function index() {
        
    }
}
?>

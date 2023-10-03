<?php

// Report All Stock All Warehouse

class One_purchase_004 extends RPT_Controller
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
        $this->report_code = 'PUR-003';
        $this->wQty = 2;

        $this->load->model("report/r_reportpurchase");
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Laporan Penjualan Detail Per Produk');
        $this->pdf->setRptSubtitle('Periode '.date('d M Y', strtotime($this->sys_input['sdate'])).' s/d '.date('d M Y', strtotime($this->sys_input['edate'])).' ');
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";
       
        $this->pdf->Output();
    }

    function search()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>$this->sys_input['page'],
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'vendor_id'=>isset($this->sys_input['vendor_id'])?$this->sys_input['vendor_id']:0
        ];
        $r = $this->r_reportpurchase->purchase_004($prm);
        $this->sys_ok($r);
    }

    function excel()
    {
        $prm = [
            'search'=>'%'.$this->sys_input['search'].'%', 
            'page'=>1, 'limit'=>99999,
            'sdate'=>$this->sys_input['sdate'],
            'edate'=>$this->sys_input['edate'],
            'vendor_id'=>isset($this->sys_input['vendor_id'])?$this->sys_input['vendor_id']:0
        ];
        $r = $this->r_reportpurchase->purchase_004($prm);

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
        $sub_total = 0;
        
        $filename = "laporan_penerimaan_barang_per_vendor_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";
        
        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_purchase_004.xls");

            // $data = [];
            $myLine = 7;
            $as = $objPHPExcel->getActiveSheet();
            foreach ($r['records'] as $k => $v)
            {
                $this->copyRowFull($as, $as, 3, $myLine);
                $as->mergeCells("A{$myLine}:I{$myLine}");
                $as->setCellValue("A{$myLine}", $v['vendor_name']);
                $myLine++;

                $total = [0, 0, 0, 0, 0];
                foreach ($v['details'] as $l => $w)
                {
                    $no = $l + 1;
                    $mapData = ['A' => $no,
                                'B' => $w->receive_date,
                                'C' => $w->receive_number,
                                'D' => $w->item_code,
                                'E' => $w->item_name,
                                'F' => $w->sales_name,
                                'G' => $w->warehouse_name,
                                'H' => $w->detail_qty,
                                'I' => $w->unit_name,
                                'J' => $w->detail_price,
                                'K' => ($w->detail_price * $w->detail_disc) + $w->detail_discrp,
                                'L' => $w->detail_ppnamount / $w->detail_qty,
                                'M' => $w->detail_total
                            ];
                    foreach ($mapData as $m => $n)
                    {
                        $as->setCellValue($m.$myLine, "{$n}");
                    }
                    $total[0] += $w->detail_total;
                    // $total[1] += $w->bill_disctotal;
                    // $total[2] += $w->bill_ppn;
                    // $total[3] += $w->bill_shipping;
                    // $total[4] += $w->bill_grand_total;

                    $myLine++;
                }
                
                // $this->copyRowFull($as, $as, 4, $myLine);
                // ->setCellValue('I1', 'Periode ' . date('d/m/Y', strtotime($sdate)) . ' - ' . date('d/m/Y', strtotime($edate)));
                $this->copyRowFull($as, $as, 5, $myLine);
                // $mapData = ['D' => '',
                //             'E' => $total[1],
                //             'F' => $total[2],
                //             'G' => $total[3],
                //             'H' => $total[4]
                //         ];
                $mapData = ['M' => $total[0]];
                $grand_total += $total[0];
                foreach ($mapData as $m => $n)
                {
                    $as->setCellValue($m.$myLine, "{$n}");
                }

                $as->mergeCells("A{$myLine}:C{$myLine}");
                $myLine++;
                $myLine++;

                
                
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

            $this->copyRowFull($as, $as, 6, $myLine);
                $as->mergeCells("A{$myLine}:L{$myLine}");
                $as->setCellValue('M'.$myLine, $grand_total);

            $as->removeRow(3,4);
            $as->setCellvalue("N2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("N3", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");
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

<?php

// Report All Stock All Warehouse

class One_iv_006_1 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    var $gsub_total = 0;
    var $gsub_total_ppn = 0;
    var $gsub_total_qty = 0;
    var $gunit = "";
    var $gitem = "";

    var $colsWidth = 4;
    var $cols = 5;
    var $curCol = 1;
    var $topY = 0;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'IV-006';

        $this->load->model("report/r_reportinventory");
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Laporan Persediaan');

        $c_name = 'Semua Kategori';
        $ctg = isset($this->sys_input['category_id']) ? isset($this->sys_input['category_id']) : 0;
        if ($ctg != 0) {
            $this->load->model('master/m_category');
            $ctg = $this->m_category->get($ctg);
            $c_name = "Kategori : " . $ctg->M_CategoryName;
        }
        $this->pdf->setRptSubtitle($c_name);
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $prm = [
            'search'=>'%', 
            'page'=>1,
            'category_id'=>isset($this->sys_input['category_id'])?$this->sys_input['category_id']:0,
            'warehouse_id'=>0
        ];
        $r = $this->r_reportinventory->iv_006($prm);

        $grand_total = 0;
        $disc_total = 0;
        $bruto_total = 0;
        $sub_total = 0;
        $sub_total_ppn = 0;
        $staff_id = 0;
        
        if ($r)
        {

            $d = $r['records'];
            
            // $data = isset($r[1])?$r[1]:[];
            // $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('P', 'A4');
            $width = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin;

            $this->topY = $this->pdf->GetY();
            $this->tableHeader($this->pdf, ['staff_name' => '', 'sub_total' => $sub_total, 'bruto_total' => $bruto_total, 'disc_total' => $disc_total]);

            $this->pdf->SetFont('Arial','', 9);

            $wQty = $this->wQty;
            $wItemName = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin;

            $cols = $this->cols; $curCol = $this->curCol;
            foreach ($d as $k => $v)
            {

                $this->pdf->SetX($this->pdf->GetX() + (($this->curCol-1)*$this->colsWidth));
                $this->pdf->Cell(1, 0.7, $k+1, 'LBR', 0, 'C', 0);
                $this->pdf->Cell(1.5, 0.7, $v['item_code'], 'LBR', 0, 'L', 0);
                // $this->pdf->Cell($width-9, 0.7, $v['item_name'], 'LBR', 0, 'L', 0);
                // $this->pdf->Cell($width-8.5, 0.7, $v['customer_name'], 'LBR', 0, 'L', 0);
                // $this->pdf->Cell(3, 0.7, number_format($v['item_bruto']), 'BR', 0, 'R', 0);
                // $this->pdf->Cell(2.5, 0.7, number_format($v['item_disc'] + $v['item_disctotal']), 'BR', 0, 'R', 0);
                $this->pdf->Cell(1.5, 0.7, number_format($v['log_a4_qty']), 'BR', 0, 'R', 0);
                // $this->pdf->Cell(2, 0.7, $v['unit_name'], 'LBR', 0, 'L', 0);
                

                $this->pdf->Ln(0.7);

                $sub_total += $v['log_a4_qty'];
                $sub_total_ppn += 0;
                $disc_total += 0;
                $bruto_total += 0;

                if ($this->pdf->GetY() > 27 && $this->curCol < $this->cols) {
                    if ($this->curCol < $this->cols) {
                        $this->curCol++;
                        $this->pdf->SetY($this->topY);
                        $this->tableHeader($this->pdf, null);
                    } else {
                        $this->curCol = 1;
                        $this->pdf->AddPage('P', 'A4');
                    }
                }
            }

            // $this->tableFooter($this->pdf, ['sub_total' => $sub_total, 'bruto_total' => $bruto_total, 'disc_total' => $disc_total]);
            // $this->pdf->Ln(0.2);
            // $this->pdf->SetFont('Arial','B', 9);
            // $this->pdf->Cell($wItemName+3, 0.7, 'TOTAL', 'BLTR', 0, 'C', 0);
            // $this->pdf->Cell($wQty, 0.7, '', 'BTR', 0, 'R', 0);
            // $this->pdf->Cell(2, 0.7, '', 'BTR', 0, 'C', 0);
            // $this->pdf->Cell($wQty, 0.7, '', 'BTR', 0, 'R', 0);
            // $this->pdf->Cell($wQty+1, 0.7, number_format($grand_total, 2), 'BTR', 0, 'R', 0);

           
            
        }

       
        $this->pdf->Output();
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
        $r = $this->r_reportinventory->iv_006($prm);
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
        $r = $this->r_reportinventory->iv_006($prm);

        $this->load->library("Excel");

        $grand_total = 0;
        $sub_total = 0;
        
        $filename = "laporan_ringkasan_persediaan_barang_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";
        
        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_iv_006.xls");

            // $data = [];
            $myLine = 5;
            $no = 1;
            $as = $objPHPExcel->getActiveSheet();
            foreach ($r['records'] as $k => $v)
            {
                if ($prm['category_id'] != 0 && $k == 0)
                    $as->setCellValue('E1', $v['category_name']);
                
                $mapData = ['A' => $no,
                            'B' => $v['category_name'],
                            'C' => $v['item_code'],
                            'D' => $v['item_name'],
                            'E' => $v['log_a4_qty'],
                            'F' => $v['unit_name'],
                            'G' => $v['item_hpp'],
                            'H' => $v['log_a4_qty'] * $v['item_hpp'],
                            // 'H' => $v['stock_qty < 0 ? $v['stock_qty : 0,
                            // 'I' => $v['stock_after_qty
                        ];
                foreach ($mapData as $m => $n)
                {
                    $as->setCellValue($m.$myLine, "{$n}");
                }

                $no++;
                $myLine++;
                $grand_total += ($v['log_a4_qty'] * $v['item_hpp']);
            }

            $this->copyRowFull($as, $as, 4, $myLine);
            $as->setCellValue('H'.$myLine, $grand_total);
            $as->setCellvalue("I2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");
            $as->removeRow(3,2);
            

                
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

    function tableHeader($me, $d = null)
    {
        $wItemName = $me->w - $me->lMargin - $me->rMargin - 9;
        $this->pdf->SetFont('Arial','', 9);

        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->SetX($me->GetX() + (($this->curCol-1)*$this->colsWidth));
        $me->Cell(1, 1, 'NO' , 'LTBR', 0, 'C', 1);
        $me->Cell(1.5, 1, 'KODE' , 'LTBR', 0, 'C', 1);
        // $me->Cell($wItemName, 1, 'NAMA BARANG' , 'LTBR', 0, 'C', 1);
        // $me->Cell(3, 1, 'BRUTO' , 'LTBR', 0, 'C', 1);
        // $me->Cell(2, 1, 'POTONGAN' , 'LTBR', 0, 'C', 1);
        $me->Cell(1.5, 1, 'QTY' , 'LTBR', 0, 'C', 1);
        // $me->Cell(2, 1, 'UNIT' , 'LTBR', 0, 'C', 1);

        // $me->Cell(4, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
        $me->Ln(1);
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

<?php

// Report All Stock All Warehouse

class One_purchase_002 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'PUR-006';
        $this->wQty = 2;

        $this->load->model("report/r_reportpurchase");
    }

    function index() {
        $this->pdf = new PDF("L","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Laporan Usia Hutang');
        $this->pdf->setRptSubtitle('Per '.date('d F Y'));
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_purchase_002( isset($this->sys_input['vendorid'])?$this->sys_input['vendorid']:0, isset($this->sys_input['term'])?$this->sys_input['term']:0 );

        $grand_total = 0;
        $sub_total = 0;
        $sub_total_ppn = 0;
        $staff_id = 0;
        $vendor_id = 0;
        $n = 1;

        if ($r)
        {
            $d = $r;
            // if (sizeof($d) > 0)
            // {
            //     $staff = $this->s_staff->get( $d[0]['staff_id'] );
            //     $staff_id = $staff['staff_id'];
            // }
            
            // $data = isset($r[1])?$r[1]:[];
            // $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');
            // $this->tableHeader($this->pdf, ['staff_name' => $staff['staff_name'], 'sub_total' => $sub_total, 'sub_total_ppn' => $sub_total_ppn]);

            // $this->pdf->SetFont('Arial','', 9);

            // $wQty = $this->wQty;
            // $wItemName = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin - 19;

            
            $w = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin ;
            foreach ($d as $k => $v)
            {
                if ($this->pdf->GetY() > 18.8)
                {
                    $this->pdf->AddPage('L', 'A4');
                    if ($vendor_id == $v['vendor_id'])
                        $this->tableHeaderCustomer($this->pdf, $v);
                }

                if ($vendor_id != $v['vendor_id'])
                {
                    $n = 1;
                    $vendor_id = $v['vendor_id'];
                    $this->tableHeaderCustomer($this->pdf, $v);
                }
            //     if ($staff_id != $v['staff_id'])
            //     {
            //         $staff_id = $v['staff_id'];
            //         $staff = $this->s_staff->get( $staff_id );

            //         $this->tableFooter($this->pdf, ['sub_total' => $sub_total, 'sub_total_ppn' => $sub_total_ppn]);
            //         $sub_total = 0;
            //         $sub_total_ppn = 0;

            //         $this->tableHeader($this->pdf, ['staff_name' => $staff['staff_name'], 'sub_total' => $sub_total, 'sub_total_ppn' => $sub_total_ppn]);
            //     }

            //     $ylimit = $this->pdf->h - 2.9;
            //     if ($this->pdf->GetY() > $ylimit)
            //     {
            //         $this->tableFooter($this->pdf, ['sub_total' => $sub_total, 'sub_total_ppn' => $sub_total_ppn]);
            //         $this->pdf->AddPage('L', 'A4');
            //         $this->tableHeader($this->pdf, ['staff_name' => $staff['staff_name'], 'sub_total' => $sub_total, 'sub_total_ppn' => $sub_total_ppn]);

            //         // $sub_total = 0;
            //         // $sub_total_ppn = 0;
            //     }

                $this->pdf->Cell(1, 0.7, ($n), 'LBR', 0, 'C', 0);
                $this->pdf->Cell($w - 23.5, 0.7, $v['bill_number'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(2, 0.7, $v['bill_date'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(2, 0.7, $v['bill_duedate'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(1, 0.7, $v['term_duration'], 'LBR', 0, 'C', 0);
                $this->pdf->Cell(2.5, 0.7, number_format($v['bill_unpaid']), 'BR', 0, 'R', 0);
                
                $bjt = $v['date_diff'] <= 0 ? $v['bill_unpaid'] : 0;
                $jt30 = $v['date_diff'] <= 30 && $v['date_diff'] > 0 ? $v['bill_unpaid'] : 0;
                $jt60 = $v['date_diff'] <= 60 && $v['date_diff'] > 30 ? $v['bill_unpaid'] : 0;
                $jt90 = $v['date_diff'] <= 90 && $v['date_diff'] > 60 ? $v['bill_unpaid'] : 0;
                $jt120 = $v['date_diff'] <= 120 && $v['date_diff'] > 90 ? $v['bill_unpaid'] : 0;
                $jtrest = $v['date_diff'] > 120 ? $v['bill_unpaid'] : 0;

                $this->pdf->Cell(2.5, 0.7, number_format($bjt), 'BR', 0, 'R', 0);
                $this->pdf->Cell(2.5, 0.7, number_format($jt30), 'BR', 0, 'R', 0);
                $this->pdf->Cell(2.5, 0.7, number_format($jt60), 'BR', 0, 'R', 0);
                $this->pdf->Cell(2.5, 0.7, number_format($jt90), 'BR', 0, 'R', 0);
                $this->pdf->Cell(2.5, 0.7, number_format($jt120), 'BR', 0, 'R', 0);
                $this->pdf->Cell(2.5, 0.7, number_format($jtrest), 'BR', 0, 'R', 0);

            //     $this->pdf->Cell(3, 0.7, $v['staff_name'], 'LBR', 0, 'L', 0);
            //     $this->pdf->Cell($wItemName, 0.7, $v['vendor_name'], 'BR', 0, 'L', 0);
            //     $this->pdf->Cell(3, 0.7, number_format($v['bill_ppn']), 'BR', 0, 'R', 0);
            //     $this->pdf->Cell(3, 0.7, number_format($v['bill_total']+$v['bill_ppn']), 'BR', 0, 'R', 0);
            //     $this->pdf->Cell(3, 0.7, number_format($v['bill_total']), 'BR', 0, 'R', 0);
                

                $this->pdf->Ln(0.7);
                $n++;

            //     $sub_total += $v['bill_total'];
            //     $sub_total_ppn += $v['bill_ppn'];
            }

            // $this->tableFooter($this->pdf, ['sub_total' => $sub_total, 'sub_total_ppn' => $sub_total_ppn]);
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

    function tableHeaderCustomer($me, $d)
    {
        $w = $me->w - $me->lMargin - $me->rMargin;
        $this->pdf->SetFont('Arial','', 9);
        
        $me->SetFillColor(222,222,222);
        $me->SetTextColor(0,0,0);
        $me->Ln(0.1);
        $me->Cell($w, 0.7, "" . strtoupper($d['vendor_name']), 'LTR', 0, 'L', 1);
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
        $this->pdf->SetFont('Arial','', 9);
        
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(0,0,0);
        $me->Cell($me->w - $me->lMargin - $me->rMargin - 12, 1, "" , '', 0, 'L', 0);
        $me->Cell(3, 0.7, "TOTAL" , 'LBR', 0, 'C', 0);
        $me->Cell(3, 0.7, number_format($d['sub_total_ppn']) , 'LBR', 0, 'R', 0);
        $me->Cell(3, 0.7, number_format($d['sub_total']+$d['sub_total_ppn']) , 'LBR', 0, 'R', 0);
        $me->Cell(3, 0.7, number_format($d['sub_total']) , 'BR', 0, 'R', 0);
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
        $me->pdf->Cell(1, 1.4, "NO" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell($w - 23.5, 1.4, "NOMOR INVOICE" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell(2, 1.4, "TANGGAL" , 'TBLR', 0, 'C', 1);
        $me->pdf->Cell(2, 0.7, "JATUH" , 'TLR', 0, 'C', 1);
        $me->pdf->Cell(1, 1.4, "TERM" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell(2.5, 0.7, "SALDO" , 'TLR', 0, 'C', 1);
        $me->pdf->Cell(2.5, 1.4, "ON GOING" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell(12.5, 0.7, "LEWAT WAKTU (Hari)" , 'TLBR', 0, 'C', 1);
        $me->pdf->Ln(0.7);

        $me->pdf->Cell($w - 20.5, 0.7, "" , '', 0, 'C', 0);
        $me->pdf->Cell(2, 0.7, "TEMPO" , 'LBR', 0, 'C', 1);
        $me->pdf->Cell(1, 0.7, "" , '', 0, 'C', 0);
        $me->pdf->Cell(2.5, 0.7, "HUTANG" , 'LBR', 0, 'C', 1);
        $me->pdf->Cell(2.5, 0.7, "" , '', 0, 'C', 0);
        $me->pdf->Cell(2.5, 0.7, "1 s/d 30" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell(2.5, 0.7, "31 s/d 60" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell(2.5, 0.7, "61 s/d 90" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell(2.5, 0.7, "91 s/d 120" , 'TLBR', 0, 'C', 1);
        $me->pdf->Cell(2.5, 0.7, "> 120" , 'TLBR', 0, 'C', 1);
        $me->pdf->Ln(0.8);
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
        $r = $this->r_reportpurchase->purchase_002($prm);
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
        $r = $this->r_reportpurchase->purchase_002($prm);

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
        
        $filename = "laporan_usia_huutang_".date('d.m.Y', strtotime($this->input->get('sdate')))."_".date('d.m.Y', strtotime($this->input->get('edate'))).".xls";
        
        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_purchase_002.xls");

            // $data = [];
            $myLine = 9;
            $as = $objPHPExcel->getActiveSheet();
            $g_total = [0, 0, 0, 0, 0, 0, 0];
            foreach ($r['records'] as $k => $v)
            {
                $this->copyRowFull($as, $as, 4, $myLine);
                $as->mergeCells("A{$myLine}:L{$myLine}");
                $as->setCellValue("A{$myLine}", $v['vendor_name']);
                $myLine++;

                $total = [0, 0, 0, 0, 0, 0, 0];
                foreach ($v['bills'] as $l => $w)
                {
                    $no = $l + 1;
                    $mapData = ['A' => $no,
                                'B' => $w->bill_number,
                                'C' => $w->bill_date,
                                'D' => $w->bill_due_date,
                                'E' => $w->term_duration,
                                'F' => $w->bill_unpaid,
                                'G' => ($w->bill_diff_date <= 0 ? $w->bill_unpaid : 0),
                                'H' => ($w->bill_diff_date <= 30 && $w->bill_diff_date > 0 ? $w->bill_unpaid : 0),
                                'I' => ($w->bill_diff_date <= 60 && $w->bill_diff_date > 30  ? $w->bill_unpaid : 0),
                                'J' => ($w->bill_diff_date <= 90 && $w->bill_diff_date > 60  ? $w->bill_unpaid : 0),
                                'K' => ($w->bill_diff_date <= 120 && $w->bill_diff_date > 90  ? $w->bill_unpaid : 0),
                                'L' => ($w->bill_diff_date > 120 ? $w->bill_unpaid : 0),
                                // 'H' => $w->bill_grand_total,
                                // 'I' => $w->bill_unpaid == 0 ? 'Lunas' : ($w->bill_paid > 0 ? 'Sebagian' : 'Baru')
                            ];
                    foreach ($mapData as $m => $n)
                    {
                        $as->setCellValue($m.$myLine, "{$n}");
                    }

                    $myLine++;
                }
                
                $total[0] = $v['total_unpaid'];
                $total[1] = $v['total_ongoing'];
                $total[2] = $v['total_30'];
                $total[3] = $v['total_60'];
                $total[4] = $v['total_90'];
                $total[5] = $v['total_120'];
                $total[6] = $v['total_rest'];
                
                $g_total[0] += $v['total_unpaid'];
                $g_total[1] += $v['total_ongoing'];
                $g_total[2] += $v['total_30'];
                $g_total[3] += $v['total_60'];
                $g_total[4] += $v['total_90'];
                $g_total[5] += $v['total_120'];
                $g_total[6] += $v['total_rest'];

                $this->copyRowFull($as, $as, 6, $myLine);
                $mapData = ['F' => $total[0],
                            'G' => $total[1],
                            'H' => $total[2],
                            'I' => $total[3],
                            'J' => $total[4],
                            'K' => $total[5],
                            'L' => $total[6]
                        ];
                foreach ($mapData as $m => $n)
                {
                    $as->setCellValue($m.$myLine, "{$n}");
                }
                $as->getStyle('A'.$myLine)
                    ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);

                $as->mergeCells("A{$myLine}:E{$myLine}");
                $myLine++;
                $myLine++;
            }

            // GRAND TOTAL
            $this->copyRowFull($as, $as, 7, $myLine);
            $as->mergeCells("A{$myLine}:E{$myLine}");
            $mapData = ['F' => $g_total[0],
                            'G' => $g_total[1],
                            'H' => $g_total[2],
                            'I' => $g_total[3],
                            'J' => $g_total[4],
                            'K' => $g_total[5],
                            'L' => $g_total[6]
                        ];
            foreach ($mapData as $m => $n)
            {
                $as->setCellValue($m.$myLine, "{$n}");
            }
            $as->getStyle('A'.$myLine)
                ->applyFromArray(['alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)]);

            $as->removeRow(4,5);
            $as->setCellvalue("M2", "=DATE(".date('Y,m,d', strtotime($this->sys_input['sdate'])).")");
            $as->setCellvalue("M4", "=DATE(".date('Y,m,d', strtotime($this->sys_input['edate'])).")");
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

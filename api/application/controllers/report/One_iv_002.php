<?php

// Report All Stock All Warehouse

class One_iv_002 extends RPT_Controller
{
    var $report_code;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'IV-002';
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true, 1);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Stock Movement');
        $this->pdf->setRptSubtitle('-');
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('report/r_report');
        $r = $this->r_report->one_iv_002( $this->sys_input['itemid'], $this->sys_input['warehouseid'], 
            date('Y-m-d', strtotime($this->sys_input['sdate'])), date('Y-m-d', strtotime($this->sys_input['edate'])) );
            
        //
        // $grand_total = 0;
        // $sub_total = 0;

        $this->pdf->SetMargins(0.7, 0.5, 0.5);
        
        if ($r)
        {
            $this->pdf->SetRptSubtitle("{$r[0][0]['item_name']} di {$r[0][0]['warehouse_name']}");
            $this->pdf->AddPage('P', 'A4');

            $this->pdf->SetFillColor(222,222,222);
            $this->pdf->SetFont('Arial','', 9);
            $this->pdf->Cell(4, 0.7, "Periode", 'BLT', 0, 'L', 1);
            $this->pdf->Cell(5, 0.7, date('d-m-Y', strtotime($this->sys_input['sdate'])) . " s/d " . date('d-m-Y', strtotime($this->sys_input['edate'])), 'BTR', 0, 'R', 0);
            $this->pdf->Ln(0.7);
            $this->pdf->Cell(4, 0.7, "Saldo Awal", 'BLT', 0, 'L', 1);
            $this->pdf->Cell(5, 0.7, number_format($r[0][0]['open_balance']), 'BTR', 0, 'R', 0);
            $this->pdf->Ln(0.7);
            $this->pdf->Cell(4, 0.7, "Transaksi Masuk", 'BL', 0, 'L', 1);
            $this->pdf->Cell(5, 0.7, number_format($r[0][0]['total_trans_in']), 'BR', 0, 'R', 0);
            $this->pdf->Ln(0.7);
            $this->pdf->Cell(4, 0.7, "Transaksi Keluar", 'BL', 0, 'L', 1);
            $this->pdf->Cell(5, 0.7, number_format($r[0][0]['total_trans_out']), 'BR', 0, 'R', 0);
            $this->pdf->Ln(0.7);
            $this->pdf->Cell(4, 0.7, "Saldo Akhir", 'BL', 0, 'L', 01);
            $this->pdf->Cell(5, 0.7, number_format($r[0][0]['close_balance']), 'BR', 0, 'R', 0);
            $this->pdf->Ln(1);
            // $d = [];
            // $items = [];
            // foreach ($r[0] as $k => $v)
            // {
            //     $items['I'.$v['item_id']] = $v;
            //     if (!isset($d['I'.$v['item_id']]))
            //         $d['I'.$v['item_id']] = [];
            //     $d['I'.$v['item_id']][$v['warehouse_code']] = $v['stock_qty'];
            // }

            
            
            // $this->pdf->SetMargins(0.7, 0.5, 0.5);
            // $this->pdf->AddPage('L', 'A4');

            // $this->tableHeader($this->pdf, $r[1]);

            // $this->pdf->SetFont('Arial','', 9);

            // $wQty = $this->wQty;
            // $wItemName = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin - 7 - ($wQty*sizeof($r[1]));

            $this->tableHeader($this->pdf);
            foreach ($r[1] as $k => $v)
            {
                $ylimit = $this->pdf->h - 2.2;
                if ($this->pdf->GetY() > $ylimit)
                {
                    $this->pdf->AddPage('P', 'A4');
                    $this->tableHeader($this->pdf);
                }

                $this->pdf->Cell(2, 0.7, date('d-m-Y', strtotime($v['log_date'])), 'LB', 0, 'L', 0);
                $this->pdf->Cell(3.8, 0.7, $v['log_ref_number'], 'LB', 0, 'L', 0);

                $text = preg_replace("/\[(supplier)\]/", $v['vendor_name'], $v['type_text']);
                $text = preg_replace("/\[(customer)\]/", $v['customer_name'], $text);
                $text = preg_replace("/\[(warehouse)\]/", $v['warehouse_name'], $text);
                $text = preg_replace("/\[(towarehouse)\]/", $v['towarehouse_name'], $text);
                
                $this->pdf->Cell(8.2, 0.7, $text, 'BL', 0, 'L', 0);
                $this->pdf->Cell(1.9, 0.7, $v['log_qty']>0?number_format($v['log_qty']):0, 'LTBR', 0, 'R', 1);
                $this->pdf->Cell(1.9, 0.7, $v['log_qty']<0?number_format($v['log_qty']):0, 'LTBR', 0, 'R', 1);
                $this->pdf->Cell(1.9, 0.7, number_format($v['log_after_qty']), 'LTBR', 0, 'R', 1);
            //     $this->pdf->Cell($wItemName, 0.7, $items[$k]['item_name'], 'BR', 0, 'L', 0);
            //     foreach ($r[1] as $l => $w)
            //     {
            //         if (isset($v[$w['warehouse_code']]))
            //             $this->pdf->Cell($wQty, 0.7, number_format($v[$w['warehouse_code']], 2), 'BR', 0, 'R', 0);
            //         else
            //             $this->pdf->Cell($wQty, 0.7, 0, 'BR', 0, 'R', 0);
            //     }
            //     $this->pdf->Cell(3, 0.7, $items[$k]['unit_name'], 'B', 0, 'C', 0);
                $this->pdf->Ln(0.7);
            }
            
        }
        else
        {
            $this->pdf->AddPage('P', 'A4');
        }
        $this->pdf->Output();
    }


    function tableHeader($me)
    {
        
        $this->pdf->SetFont('Arial','', 9);
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'C', 1);
        $me->Cell(3.8, 1, 'NO REF' , 'LTBR', 0, 'L', 1);
        $me->Cell(8.2, 1, 'DESKRIPSI' , 'LTBR', 0, 'L', 1);
        $me->Cell(1.9, 1, 'QTY IN' , 'LTBR', 0, 'R', 1);
        $me->Cell(1.9, 1, 'QTY OUT' , 'LTBR', 0, 'R', 1);
        $me->Cell(1.9, 1, 'SALDO' , 'LTBR', 0, 'R', 1);
        // $me->Cell($wItemName, 1, 'NAMA ITEM' , 'LTBR', 0, 'C', 1);
        
        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
        $me->Ln(1);
    }

    
}
?>

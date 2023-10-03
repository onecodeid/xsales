<?php

// Report All Stock All Warehouse

class One_an_002 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'AN-002';
        $this->wQty = 2;
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Rekapitulasi Transaksi Harian');
        $this->pdf->setRptSubtitle("Tanggal " . date("d M Y", strtotime($this->sys_input['sdate'])) . " s/d " . date("d M Y", strtotime($this->sys_input['edate'])));

        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('analytic/an_inventory');
        $r = $this->an_inventory->recapt_daily(
            date("Y-m-d", strtotime($this->sys_input['sdate'])), 
            date("Y-m-d", strtotime($this->sys_input['edate'])) );

        // $total = 0;
        // $sub_total = 0;

        if ($r)
        {
            $d = $r['records'];
            $recapt_sales = [];
            $recapt_expedition = [];
            // $items = [];
            // foreach ($r[0] as $k => $v)
            // {
            //     $items['I'.$v['item_id']] = $v;
            //     if (!isset($d['I'.$v['item_id']]))
            //         $d['I'.$v['item_id']] = [];
            //     $d['I'.$v['item_id']][$v['warehouse_code']] = ['qty'=>$v['stock_qty'],'hpp'=>$v['item_hpp']];
            // }

            
            // $data = isset($r[1])?$r[1]:[];
            // $r = $r[0][0];
            $this->pdf->SetMargins(0.7, 0.5, 0.5);
            $this->pdf->AddPage('L', 'A4');

            $this->tableHeader($this->pdf);

            $this->pdf->SetFont('Arial','', 8);

            $wQty = $this->wQty;
            $wItemName = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin - 22;

            $ylimit = $this->pdf->h - 2.2;
            
            foreach ($d as $k => $v)
            {
                if ($this->pdf->GetY() > $ylimit)
                {
                    $this->pdf->AddPage('L', 'A4');
                    $this->tableHeader($this->pdf);
                }

                $row_height = 0;
                for ($i = 0; $i < $v['rowspan']; $i++)
                {
                    if ( $this->pdf->GetY() + $row_height < $ylimit )
                        $row_height += 0.7;    
                }
                // $row_height = 0.7 * $v['rowspan'];
                // if ( $this->pdf->GetY() + $row_height > $ylimit )
                //     $row_height = $this->pdf->GetY() + $row_height - $ylimit;

                $this->pdf->Cell(2, $row_height, $v['invoice_date'], 'LTBR', 0, 'L', 0);
                $this->pdf->Cell(2, $row_height, $v['staff_name'], 'LTBR', 0, 'L', 0);
                $this->pdf->Cell(4.5, $row_height, $v['customer_name'], 'LTBR', 0, 'L', 0);

                $this->pdf->Cell($wItemName + 3.5, 0.7, '', '', 0, 'L', 0);
                $this->pdf->Cell(3, $row_height, $v['payment_name'], 'BTR', 0, 'L', 0);
                $this->pdf->Cell(3, $row_height, $v['delivery_name'], 'BTR', 0, 'L', 0);
                $this->pdf->Cell(4, $row_height, $v['expedition_name'], 'BTR', 0, 'L', 0);

                $this->pdf->SetX( $this->pdf->lMargin );
                foreach ($v['items'] as $l => $w)
                {
                    if ($this->pdf->GetY() > $ylimit)
                    {
                        $this->pdf->AddPage('L', 'A4');
                        $this->tableHeader($this->pdf);
                    }

                    $this->pdf->Cell(8.5, 0.7, '', 'L', 0, 'L', 0);
                    $this->pdf->Cell($wItemName, 0.7, $w->item_name, 'LBR', 0, 'L', 0);
                    $this->pdf->Cell(2, 0.7, number_format($w->item_qty), 'BR', 0, 'R', 0);
                    $this->pdf->Cell(1.5, 0.7, $w->unit_name, 'BR', 0, 'R', 0);
                    $this->pdf->Ln(0.7);
                }
                
                // $this->pdf->Cell(3, 0.7, number_format($v['expedition_name']), 'BR', 0, 'R', 0);

                // $this->pdf->Ln(0.7);
                if (!isset($recapt_sales[$v['staff_name']]))
                    $recapt_sales[$v['staff_name']] = ["cnt"=>1];
                else
                    $recapt_sales[$v['staff_name']]["cnt"]++;

                if (!isset($recapt_expedition[$v['expedition_name']]))
                    $recapt_expedition[$v['expedition_name']] = ["cnt"=>1];
                else
                    $recapt_expedition[$v['expedition_name']]["cnt"]++;

                
            }

            $sales_cnt = 0;
            $this->pdf->Ln(0.7);
            $this->tableHeaderSummary($this->pdf, $recapt_sales);
            foreach ($recapt_sales as $k => $v)
            {
                $this->pdf->Cell(3, 0.7, number_format($v['cnt']), 'LBR', 0, 'C', 0);
                $sales_cnt += $v['cnt'];
            }
            $this->pdf->Cell(3, 0.7, $sales_cnt, 'LBR', 0, 'L', 0);

            $exp_cnt = 0;
            $this->pdf->Ln(1.4);
            $this->tableHeaderSummary($this->pdf, $recapt_expedition, "Summary Transaksi per Ekspedisi");
            foreach ($recapt_expedition as $k => $v)
            {
                $this->pdf->Cell(3, 0.7, number_format($v['cnt']), 'LBR', 0, 'C', 0);
                $exp_cnt += $v['cnt'];
            }
            $this->pdf->Cell(3, 0.7, $exp_cnt, 'LBR', 0, 'L', 0);
            
        }

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
        $wQty = $this->wQty;
        $wItemName = $me->w - $me->lMargin - $me->rMargin - 22;
        $this->pdf->SetFont('Arial','', 8);
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->Cell(2, 1, 'TANGGAL' , 'LTBR', 0, 'L', 1);
        $me->Cell(2, 1, 'SALES' , 'LTBR', 0, 'L', 1);
        $me->Cell(4.5, 1, 'CUSTOMER' , 'LTBR', 0, 'L', 1);
        $me->Cell($wItemName, 1, 'NAMA ITEM' , 'LTBR', 0, 'L', 1);
        $me->Cell(2, 1, 'QTY' , 'LTBR', 0, 'R', 1);
        $me->Cell(1.5, 1, 'UNIT' , 'LTBR', 0, 'R', 1);
        $me->Cell(3, 1, 'PEMBAYARAN' , 'LTBR', 0, 'L', 1);
        $me->Cell(3, 1, 'PENGIRIMAN' , 'LTBR', 0, 'L', 1);
        $me->Cell(4, 1, 'EKSEPEDISI' , 'LTBR', 0, 'L', 1);

        // $me->Cell(4, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
        $me->Ln(1);
    }

    function tableHeaderSummary($me, $data, $title = "Summary Transaksi per Sales")
    {
        $this->pdf->SetFont('Arial','', 10);

        $me->Cell(5, 0.7, strtoupper($title), '', 0, 'L', 1);
        $me->Ln(0.7);

        $this->pdf->SetFont('Arial','', 8);
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);

        foreach ($data as $k => $v)
        {
            $me->Cell(3, 1, strtoupper($k), 'LTBR', 0, 'C', 1);
        }
        $me->Cell(3, 1, 'T O T A L' , 'LTBR', 0, 'C', 1);
        
        // $me->Cell(4, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
        $me->Ln(1);
    }    
}
?>

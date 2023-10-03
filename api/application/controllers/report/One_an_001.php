<?php

// Report All Stock All Warehouse

class One_an_001 extends RPT_Controller
{
    var $report_code;
    var $wQty;

    function __construct()
    {
        parent::__construct();

        $this->load->library("pdf");
        $this->report_code = 'AN-001';
        $this->wQty = 2;
    }

    function index() {
        $this->pdf = new PDF("P","cm",array(21,29.7));
        $this->pdf->SetAutoPageBreak(true,1);

        $this->load->model('master/m_warehouse');
        $wh = $this->m_warehouse->get($this->sys_input['warehouse']);

        $this->pdf->rptclass = $this;
        $this->pdf->setRptTitle('Produk Diagnostik');
        if ( $this->sys_input['warehouse'] == 0 )
            $this->pdf->setRptSubtitle('Semua Gudang');
        else
            $this->pdf->setRptSubtitle('Gudang ' . $wh->warehouse_name);
        $this->pdf->header_func = "my_header_recapt";
        $this->pdf->footer_func = "my_footer";

        $this->pdf->SetFont('Arial','', 11);

        // Get data
        $this->load->model('analytic/an_inventory');
        $r = $this->an_inventory->pareto(
            date("Y-m-d", strtotime($this->sys_input['sdate'])), 
            date("Y-m-d", strtotime($this->sys_input['edate'])),
            isset($this->sys_input['warehouse'])?$this->sys_input['warehouse']:0,
            isset($this->sys_input['orderby'])?$this->sys_input['orderby']:'omzet_freq desc');

        // $total = 0;
        // $sub_total = 0;

        if ($r)
        {
            $d = $r['records'];
            $totals = ["qty"=>0, "freq"=>0];
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
            $this->pdf->AddPage('P', 'A4');

            $this->tableHeader($this->pdf);

            $this->pdf->SetFont('Arial','', 9);

            $wQty = $this->wQty;
            $wItemName = $this->pdf->w - $this->pdf->lMargin - $this->pdf->rMargin - 13;

            $ylimit = $this->pdf->h - 2.2;
            
            foreach ($d as $k => $v)
            {
                if ($this->pdf->GetY() > $ylimit)
                {
                    $this->pdf->AddPage('P', 'A4');
                    $this->tableHeader($this->pdf);

                    // $sub_total = 0;
                }

                $this->pdf->Cell(3, 0.7, $v['warehouse_name'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell(2.5, 0.7, $v['item_code'], 'LBR', 0, 'L', 0);
                $this->pdf->Cell($wItemName, 0.7, $v['item_name'], 'BR', 0, 'L', 0);
                $this->pdf->Cell(2.5, 0.7, number_format($v['omzet_qty']), 'BR', 0, 'R', 0);
                $this->pdf->Cell(2.5, 0.7, number_format($v['omzet_freq']), 'BR', 0, 'R', 0);
                $this->pdf->Cell(2.5, 0.7, number_format($v['omzet_av']), 'BR', 0, 'R', 0);

                $this->pdf->Ln(0.7);

                $totals["qty"] += $v['omzet_qty'];
                $totals["freq"] += $v["omzet_freq"];
            }

            $this->pdf->Cell(5.5 + $wItemName, 0.7, "TOTAL", 'LBR', 0, 'L', 0);
            $this->pdf->Cell(2.5, 0.7, number_format($totals["qty"]), 'BR', 0, 'R', 0);
            $this->pdf->Cell(2.5, 0.7, number_format($totals['freq']), 'BR', 0, 'R', 0);
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
        $wItemName = $me->w - $me->lMargin - $me->rMargin - 13;
        $this->pdf->SetFont('Arial','', 9);
        $me->SetFillColor(0,0,0);
        $me->SetTextColor(255,255,255);
        $me->Cell(3, 1, 'GUDANG' , 'LTBR', 0, 'L', 1);
        $me->Cell(2.5, 1, 'KODE ITEM' , 'LTBR', 0, 'L', 1);
        $me->Cell($wItemName, 1, 'NAMA ITEM' , 'LTBR', 0, 'L', 1);
        $me->Cell(2.5, 1, 'QTY SALES' , 'LTBR', 0, 'R', 1);
        $me->Cell(2.5, 1, 'FREK SALES' , 'LTBR', 0, 'R', 1);
        $me->Cell(2.5, 1, 'RATA-RATA' , 'LTBR', 0, 'R', 1);

        // $me->Cell(4, 0.7, 'TOTAL' , 'TBR', 0, 'C',1);
        $me->SetFillColor(255,255,255);
        $me->SetTextColor(0,0,0);
        $me->Ln(1);
    }

    
}
?>

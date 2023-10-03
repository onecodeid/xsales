<?php

// Report Fee / Komisi Per Admin
//

class One_an_001_excel extends RPT_Controller
{
    var $report_code;
    // var $pdf;

    function __construct()
    {
        parent::__construct();

        $this->load->library("Excel");
        $this->report_code = 'AN-001';
    }

    function index() {
        // Get data
        $this->load->model('report/r_report');
        
        // if (!isset($this->sys_input['uid']))
        //     $uid = $this->sys_user['user_id'];
        // else
        //     $uid = $this->sys_input['uid'];

        // Get data
        $this->load->model('analytic/an_inventory');
        $r = $this->an_inventory->pareto(
            date("Y-m-d", strtotime($this->sys_input['sdate'])), 
            date("Y-m-d", strtotime($this->sys_input['edate'])),
            isset($this->sys_input['warehouse'])?$this->sys_input['warehouse']:0,
            isset($this->sys_input['orderby'])?$this->sys_input['orderby']:'omzet_freq desc');

        $this->load->model('master/m_warehouse');
        $wh = $this->m_warehouse->get($this->sys_input['warehouse']);

        $filename = "laporan_produk_diagnostik_".date('d.m.Y', strtotime($this->sys_input['sdate']))."_".date('d.m.Y', strtotime($this->sys_input['edate'])).".xls";
        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_an_001.xls");

            $data = [];
            
            $row_start = 6;
            $empty = [];
            foreach ($r['records'] as $k => $v)
            {
                $data[] = [$k+1, 
                        $v['warehouse_name'],
                        $v['item_code'],
                        $v['item_name'],
                        $v['omzet_qty'],
                        $v['omzet_freq'],
                        $v['omzet_av']];
            }
            
            $baseRow = 2;
            $as = $objPHPExcel->getActiveSheet();
            $as->fromArray($data, null, 'B'.$row_start);
            // $as->fromArray($month2, null, 'H'.$row_start);
            // $as->fromArray($month3, null, 'J'.$row_start);
            // $as->fromArray($empty, null, 'F10');
            // $as->removeRow(10, 5);

            $as->setCellValue('G2', 'PERIODE ' . date("d M Y", strtotime($this->sys_input['sdate'])) . ' s/d ' . date("d M Y", strtotime($this->sys_input['edate'])));
            if ( $this->sys_input['warehouse'] == 0 )
                $as->setCellValue('G3', 'SEMUA GUDANG');
            else
                $as->setCellValue('G3', 'GUDANG ' . $wh->warehouse_name);
            // $as->setCellValue('F4', $r[0][0]['month1']);
            // $as->setCellValue('H4', $r[0][0]['month2']);
            // $as->setCellValue('J4', $r[0][0]['month3']);

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            // $objWriter->save(str_replace('api/application/controllers/report/', 'uploads/reports/', str_replace('.php', '.xls', __FILE__)));
            $objWriter->save("/home/one/Web/one-account/uploads/reports/".$filename);
            
            $objPHPExcel->disconnectWorksheets();// Good to disconnect
            $objPHPExcel->garbageCollect(); // Add this too
            unset($objWriter, $objPHPExcel);

            // echo json_encode(["status"=>"OK", 
            //     "data"=>"http://{$_SERVER['SERVER_NAME']}/pungkook/api/excel/".str_replace('.php', '.xls', pathinfo(__FILE__, PATHINFO_BASENAME))]);

        }

        $this->sys_ok(["report_url"=>$this->REPORT_EXCEL_URL.$filename]);
    }
}
?>

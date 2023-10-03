<?php

// Report Fee / Komisi Per Admin
//

class One_master_001_excel extends RPT_Controller
{
    var $report_code;
    // var $pdf;

    function __construct()
    {
        parent::__construct();

        $this->load->library("Excel");
        $this->report_code = 'MASTER-001';
    }

    function index() {
        // Get data
        $this->load->model('report/r_report');
        
        if (!isset($this->sys_input['uid']))
            $uid = $this->sys_user['user_id'];
        else
            $uid = $this->sys_input['uid'];

        $provinceid = 0;
        $cityid = 0;
        $levelid = 0;
        if (isset($this->sys_input['city_id'])) $cityid = $this->sys_input['city_id'];
        if (isset($this->sys_input['province_id'])) $provinceid = $this->sys_input['province_id'];
        if (isset($this->sys_input['level_id'])) $levelid = $this->sys_input['level_id'];

        $r = $this->r_report->one_master_001( $uid, $provinceid, $cityid, $levelid );
        //
        $grand_total = 0;
        $sub_total = 0;
        $curr_province = 0;
// print_r($r);
        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template.xls");

            $data = [];
            foreach ($r[1] as $k => $v)
            {
                $data[] = [$k+1, 
                        $v['customer_name'],
                        $v['customer_address'],
                        $v['city_name'],
                        $v['province_name'],
                        $v['customer_phone'],
                        $v['level_name']];
            }

            $baseRow = 2;
            $as = $objPHPExcel->getActiveSheet();
            $as->fromArray($data, null, 'A4');

            // $as->setCellValue('A1', 'Laporan Pertanggungjawaban Mutasi Barang Baku dan Bahan Penolong')
            //     ->setCellValue('I1', 'Periode ' . date('d/m/Y', strtotime($sdate)) . ' - ' . date('d/m/Y', strtotime($edate)));

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save(str_replace('one-sales/api/application/controllers/report/', 'uploads/reports/', str_replace('.php', '.xls', __FILE__)));

            // echo json_encode(["status"=>"OK", 
            //     "data"=>"http://{$_SERVER['SERVER_NAME']}/pungkook/api/excel/".str_replace('.php', '.xls', pathinfo(__FILE__, PATHINFO_BASENAME))]);

        }   
    }
}
?>

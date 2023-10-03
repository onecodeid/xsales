<?php

// Report Fee / Komisi Per Admin
//

class One_sales_007_excel extends RPT_Controller
{
    var $report_code;
    // var $pdf;

    function __construct()
    {
        parent::__construct();

        $this->load->library("Excel");
        $this->report_code = 'SALES-007';
    }

    function index() {
        // Get data
        $this->load->model('report/r_report');
        
        // if (!isset($this->sys_input['uid']))
        //     $uid = $this->sys_user['user_id'];
        // else
        //     $uid = $this->sys_input['uid'];

        $date = 0;
        $levelid = 0;
        if (isset($this->sys_input['date'])) $date = $this->sys_input['date'];
        if (isset($this->sys_input['level_id'])) $levelid = $this->sys_input['level_id'];

        $r = $this->r_report->one_sales_007( $date, $levelid );
        if ($r)
        {
            // EXCEL Starts Here
            /** PHPExcel_IOFactory */
            // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("./assets/template_sales_007.xls");

            $data = [];
            $month2 = [];
            $month3 = [];
            $row_start = 6;
            $empty = [];
            foreach ($r[1] as $k => $v)
            {
                $data[] = [$k+1, 
                        $v['M_CustomerName'],
                        $v['M_CustomerLevelName'],
                        $v['F_TargetOmzetAmount'],
                        $v['month1'],
                        '',
                        $v['month2'],
                        '',
                        $v['month3']];
                // $month2[] = [$v['month2']];
                // $month3[] = [$v['month3']];
            }

            for ($i=0; $i<750; $i++) {
                $empty[] = [0, 'X', '', '', '', ''];
            }
            

            $baseRow = 2;
            $as = $objPHPExcel->getActiveSheet();
            $as->fromArray($data, null, 'B'.$row_start);
            // $as->fromArray($month2, null, 'H'.$row_start);
            // $as->fromArray($month3, null, 'J'.$row_start);
            // $as->fromArray($empty, null, 'F10');
            // $as->removeRow(10, 5);

            $as->setCellValue('F4', $r[0][0]['month1']);
            $as->setCellValue('H4', $r[0][0]['month2']);
            $as->setCellValue('J4', $r[0][0]['month3']);

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save(str_replace('one-sales/api/application/controllers/report/', 'uploads/reports/', str_replace('.php', '.xls', __FILE__)));

            // echo json_encode(["status"=>"OK", 
            //     "data"=>"http://{$_SERVER['SERVER_NAME']}/pungkook/api/excel/".str_replace('.php', '.xls', pathinfo(__FILE__, PATHINFO_BASENAME))]);

        }   
    }
}
?>

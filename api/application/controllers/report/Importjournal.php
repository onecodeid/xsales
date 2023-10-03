<?php

// Report Perubahan Modal
//

class Importjournal extends RPT_Controller
{
    var $report_code;
    var $balances;
    var $width;
    var $accs;

    function __construct()
    {
        parent::__construct();
    }

    function index()
    { 
       
        // EXCEL Starts Here
        /** PHPExcel_IOFactory */
        // require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

        
        $this->load->library("Excel");
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objPHPExcel = $objReader->load("./assets/data_journal_0323.xls");

        $as = $objPHPExcel->getActiveSheet();

        $rowTotal = 6513; // $as->getHighestDataRow();
        $rowNumber = 7;
        $row = $as->getRowIterator($rowNumber)->current();
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        $headers = [];
        $data = [];
        $raw = [];

        $this->db->query("truncate _tmp_journal_01");
        $map = ['A'=>'acc_no','B'=>'acc_name','C'=>'ledger_note','D'=>'debit','E'=>'credit'];

        $ext = [];
        $date = null;
        $header = "";
        $journal_no = "";

        for ($i=$rowNumber; $i<= $rowTotal; $i++)
        {
            $row = $as->getRowIterator($i)->current();
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            

            // $data[] = [];
            $x = [];
            foreach ($cellIterator as $cell) 
            {
                $col = $cell->getColumn();
                $idx = $map[$col];
                $x[$idx] = $cell->getValue();
                // $x = ['col'=>$cell->getColumn(), 'row'=>$cell->getRow(), 'val'=>$cell->getValue(), 'cell'=>$cell->GetColumn().$cell->GetRow()];
                // if ($i==1)
                //     $headers[] = ['col'=>$cell->getColumn(), 'row'=>$cell->getRow(), 'val'=>$cell->getValue(), 'cell'=>$cell->GetColumn().$cell->GetRow()];
                // else
                // {
                //     $ext[$cell->getColumn()] = $cell->getValue();
                // }
                    // $data[$i-1][$cell->getColumn().$cell->getRow()] = $cell->getValue();
            }

            if (preg_match('/(\#)[A-Z0-9\-]+/', $x['acc_name']) && preg_match('/(\|)/', $x['acc_name']))
            {
                $e1 = explode("|", $x['acc_name']);
                $e2 = explode("#", $e1[0]);

                $journal_no = trim($e2[1]);
                $header = trim($e2[0]);
                $date = preg_replace("/\//", "-", $e1[1]);
                $date = date("Y-m-d", strtotime($date));
            }
            else if (!preg_match('/(Total)/', $x['acc_no']))
            {
                $x['date'] = $date;
                $x['header'] = $header;
                $x['journal_no'] = $journal_no;
                $ext[] = $x;
            }
            

            

            // $raw[] = $ext;
            // if ($i==1) continue;
            // $null = false;
            // foreach ($ext as $k => $v)
            // { 
            //     if ($v == null && $v == '' && ($k != 'G' && $k != 'H' && $k != 'B') )
            //         $null = true;
                
            //     if ($v == null)
            //         $ext[$k] = '';
            // }
                
            // if (!$null) $data[] = $ext;
            
        }
        $this->db->insert_batch('_tmp_journal_01', $ext);
        echo json_encode($ext);

        $this->db->query("truncate _tmp_t_journal");
        $this->db->query("truncate _tmp_t_journaldetail");
        
        $this->db->query("
            INSERT INTO _tmp_t_journal(T_JournalDate,T_JournalNumber,T_JournalNote)
            SELECT `date`, journal_no, header
            FROM _tmp_journal_01 GROUP BY journal_no
        ");

        $this->db->query("
        INSERT INTO _tmp_t_journaldetail(T_JournalDetailT_JournalID,T_JournalDetailM_AccountID,T_JournalDetailDebit,T_JournalDetailCredit,T_JournalDetailLedgerNote)
        SELECT T_JournalID, M_AccountID, debit, credit, ledger_note
        FROM _tmp_journal_01
        JOIN _tmp_t_journal ON journal_no = T_JournalNumber
        JOIN _tmp_journal_acc ON acc_no = journal_acc_no
        JOIN m_account ON erp_acc_no = M_AccountCode AND M_AccountIsActive = 'Y'
        ORDER BY T_JournalID
        ");
        // $this->sys_ok(["report_url"=>$this->REPORT_EXCEL_URL.$filename]);
    }
}
?>

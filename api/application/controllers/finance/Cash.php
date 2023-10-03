<?php

class Cash extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('finance/f_cash');
    }

    function save()
    {   
        if (isset($this->sys_input['cash_id']))
            $r = $this->f_cash->save( $this->sys_input, $this->sys_input['cash_id'], $this->sys_user['user_id'] );
        else
            $r = $this->f_cash->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK") {
            $data = json_decode($r->data);
            $outp = "assets/images/receipt_" . preg_replace("/[\/]+/", "_", $data->cash_number) . ".jpg";

            $img = '';
            $uri = getcwd().'/../';

            if (file_exists($uri.$outp))
            {
                unlink($uri.$outp);
            }

            if (isset($this->sys_input['cash_img'])) {
                if ($this->sys_input['cash_img'] != '') {
                    $img = $this->save_img($this->sys_input['cash_img'], $uri, $outp);
                }
            }
            $this->f_cash->save_img($data->cash_id, $img);

            $this->sys_ok($r->data);
        }
            
        else
            $this->sys_error($r->message);
    }

    function save_batch()
    {   
        $hdata = json_decode($this->sys_input['hdata']);
        $md5 = md5($this->sys_user['user_id'] . '-' . date('YmdHis'));

        $min_date = null;
        $max_date = null;
        foreach ($hdata as $k => $v)
        {
            if (preg_match("/(\/)/", $v->cash_date))
            {
                $v->cash_date = preg_replace("/[\/]+/", "-", $v->cash_date);
                $hdata[$k]->cash_date = date('Y-m-d',strtotime(($v->cash_date))); 
            }
                
            else   
                $hdata[$k]->cash_date = date('Y-m-d',strtotime('1899-12-31+'.($v->cash_date-1).' days'));
            $hdata[$k]->cash_md5 = $md5;

            if (strtotime($min_date) > strtotime($hdata[$k]->cash_date) || $min_date == null) $min_date = $hdata[$k]->cash_date;
            if (strtotime($max_date) < strtotime($hdata[$k]->cash_date) || $min_date == null) $max_date = $hdata[$k]->cash_date;
        }
        $this->sys_input['hdata'] = json_encode($hdata);

        $r = $this->f_cash->save_batch( $this->sys_input, $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
        {
            $r->data->min_date = $min_date;
            $r->data->max_date = $max_date;

            $this->sys_ok($r->data);
        }
            
        else
            $this->sys_error($r->message);
    }

    function search()
    {
        $inp = $this->sys_input;
        $r = $this->f_cash->search([
            'search'=>'%'.$inp['search'].'%', 
            'page'=>$inp['page'], 
            'sdate'=>$inp['sdate'], 
            'edate'=>$inp['edate'],
            'account_id'=>isset($inp['account_id'])?$inp['account_id']:0,
            'md5'=>isset($inp['md5'])?$inp['md5']:'']);
        $this->sys_ok($r);
    }

    function search_history()
    {
        $inp = $this->sys_input;
        $r = $this->f_cash->search([
            'search'=>'%'.$inp['search'].'%', 
            'page'=>$inp['page'], 
            'sdate'=>$inp['sdate'], 
            'edate'=>$inp['edate'],
            'account_id'=>isset($inp['account_id'])?$inp['account_id']:0,
            'import'=>true,
            'md5'=>isset($inp['md5'])?$inp['md5']:'']);
        $this->sys_ok($r);
    }

    function search_id()
    {
        $r = $this->f_cash->search_id($this->sys_input['cash_id']);
        $this->sys_ok($r);
    }

    function save_img($base64_string, $uri, $output_file)
    {
        return $this->base64_to_jpeg_2($base64_string, $uri, $output_file);
    }

    function base64_to_jpeg_2($base64_string, $uri, $output_file) {
        // open the output file for writing
        $ifp = fopen( $uri.$output_file, 'wb' ); 
    
        // split the string on commas
        // $data[ 0 ] == "data:image/png;base64"
        // $data[ 1 ] == <actual base64 string>
        $data = explode( ',', $base64_string );
    
        // we could add validation here with ensuring count( $data ) > 1
        fwrite( $ifp, base64_decode( $data[ 1 ] ) );
    
        // clean up the file resource
        fclose( $ifp ); 
    
        return $output_file; 
    }

    function delete()
    {
        if (isset($this->sys_input['cash_id']))
            $r = $this->f_cash->delete( $this->sys_input['cash_id'], $this->sys_user['user_id'] );
        else {
            $this->sys_error("System Error");
            return;
        }
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function upload ()
    {
        $file = $_FILES["file"];

        $this->load->library("Excel");
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objPHPExcel = $objReader->load($file['tmp_name']);

        $as = $objPHPExcel->getActiveSheet();

        $rowTotal = $as->getHighestDataRow();
        $rowNumber = 1;
        $row = $as->getRowIterator($rowNumber)->current();
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        $headers = [];
        $data = [];
        $raw = [];
        for ($i=1; $i<= $rowTotal; $i++)
        {
            $row = $as->getRowIterator($i)->current();
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            // $data[] = [];
            $ext = [];
            foreach ($cellIterator as $cell) 
            {
                if ($i==1)
                    $headers[] = ['col'=>$cell->getColumn(), 'row'=>$cell->getRow(), 'val'=>$cell->getValue(), 'cell'=>$cell->GetColumn().$cell->GetRow()];
                else
                {
                    $ext[$cell->getColumn()] = $cell->getValue();
                }
                    // $data[$i-1][$cell->getColumn().$cell->getRow()] = $cell->getValue();
            }

            $raw[] = $ext;
            if ($i==1) continue;
            $null = false;
            foreach ($ext as $k => $v)
            { 
                if ($v == null && $v == '' && ($k != 'G' && $k != 'H' && $k != 'B') )
                    $null = true;
                
                if ($v == null)
                    $ext[$k] = '';
            }
                
            if (!$null) $data[] = $ext;
            
        }

        // file_put_contents("/tmp/cash.json", json_encode($raw));
        // $this->db->query("insert into _tmp_txt(`txt`) values(?)", [json_encode($raw)]);
        // foreach ($cellIterator as $cell) {
        //     $headers[] = ['col'=>$cell->getColumn(), 'row'=>$cell->getRow(), 'val'=>$cell->getValue(), 'cell'=>$cell->GetColumn().$cell->GetRow()];
        // }

        // $data = [];
        // $lastColumn = $as->getHighestColumn();
        // foreach($as->getRowIterator() as $rowIndex => $row) {
        //     // Convert the cell data from this row to an array of values
        //     //    just as though you'd retrieved it using fgetcsv()
        //     $data[] = $as->rangeToArray('A'.$rowIndex.':'.$lastColumn.$rowIndex);
        // }

        $this->sys_ok(['records'=>['headers'=>$headers,'data'=>$data]]);

// $target_dir = "uploads/";

// $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
// $uploadOk = 1;
// $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// // Check if image file is a actual image or fake image
// if(isset($_POST["submit"])) {
//   $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
//   if($check !== false) {
//     echo "File is an image - " . $check["mime"] . ".";
//     $uploadOk = 1;
//   } else {
//     echo "File is not an image.";
//     $uploadOk = 0;
//   }
// }

// // Check if file already exists
// if (file_exists($target_file)) {
//   echo "Sorry, file already exists.";
//   $uploadOk = 0;
// }

// // Check file size
// if ($_FILES["fileToUpload"]["size"] > 500000) {
//   echo "Sorry, your file is too large.";
//   $uploadOk = 0;
// }

// // Allow certain file formats
// if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
// && $imageFileType != "gif" ) {
//   echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
//   $uploadOk = 0;
// }

// // Check if $uploadOk is set to 0 by an error
// if ($uploadOk == 0) {
//   echo "Sorry, your file was not uploaded.";
// // if everything is ok, try to upload file
// } else {
//   if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
//     echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
//   } else {
//     echo "Sorry, there was an error uploading your file.";
//   }
// }

    }
}
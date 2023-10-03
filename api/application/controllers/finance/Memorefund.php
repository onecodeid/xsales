<?php

class Memorefund extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('finance/f_memorefund');
    }

    // function search()
    // {
    //     $inp = $this->sys_input;
    //     $r = $this->f_memo->search([
    //         'search'=>'%'.$inp['search'].'%', 
    //         'page'=>$inp['page'], 
    //         'sdate'=>$inp['sdate'], 
    //         'edate'=>$inp['edate'],
    //         'account_id'=>isset($inp['account_id'])?$inp['account_id']:0]);
    //     $this->sys_ok($r);
    // }

    function search_id()
    {
        $r = $this->f_memorefund->search_id($this->sys_input['refund_id']);
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
        if (isset($this->sys_input['memo_id']))
            $r = $this->f_memo->delete( $this->sys_input['memo_id'], $this->sys_user['user_id'] );
        else {
            $this->sys_error("System Error");
            return;
        }
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function refund()
    {
        $this->load->model('finance/f_memorefund');

        if (isset($this->sys_input['refund_id']))
            $r = $this->f_memorefund->save( $this->sys_input, $this->sys_input['refund_id'], $this->sys_user['user_id'] );
        else
            $r = $this->f_memorefund->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK") {
            $this->sys_ok($r->data);
        }
            
        else
            $this->sys_error($r->message);
    }
}
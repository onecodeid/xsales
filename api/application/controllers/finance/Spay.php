<?php

class Spay extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('finance/f_spay');
    }

    // function search()
    // {
    //     $inp = $this->sys_input;
    //     $r = $this->l_invoice->search([
    //         'search'=>'%'.$inp['search'].'%', 
    //         'page'=>$inp['page'], 
    //         'sdate'=>$inp['sdate'], 
    //         'edate'=>$inp['edate'],
    //         'account_id'=>isset($inp['account_id'])?$inp['account_id']:0]);
    //     $this->sys_ok($r);
    // }

    function search_id()
    {
        $r = $this->f_pay2->search_id($this->sys_input['pay_id']);
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
        if (isset($this->sys_input['pay_id']))
            $r = $this->f_spay->delete( $this->sys_input['pay_id'], $this->sys_user['user_id'] );
        else {
            $this->sys_error("System Error");
            return;
        }
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function save()
    {
        $this->load->model('finance/f_spay');

        if (isset($this->sys_input['pay_id']))
            $r = $this->f_spay->save( $this->sys_input, $this->sys_input['pay_id'], $this->sys_user['user_id'] );
        else
            $r = $this->f_spay->save( $this->sys_input, 0, $this->sys_user['user_id'] );
        
        if ($r->status == "OK") {
            $this->sys_ok($r->data);
        }
            
        else
            $this->sys_error($r->message);
    }

    function get_history()
    {
        $r = $this->f_pay2->get_history($this->sys_input['invoice_id']);
        $this->sys_ok($r);
    }
}
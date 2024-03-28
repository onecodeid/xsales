<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('selected/selected_model');
    }
    public function get_data() {
        $data_json = $this->selected_model->get_data();

        $response = [
            'status' => "OK",
            'data' => $data_json
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    public function save_data() {
        if ($this->selected_model->save_data($this->sys_input["selectedData"])) {
            $response = [
                'status' => "OK",
                'message' => "Data berhasil dipilih."
            ];
        } else {
            $response = [
                'status' => "Error",
                'message' => "Terjadi kesalahan saat memilih data."
            ];
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }    
}
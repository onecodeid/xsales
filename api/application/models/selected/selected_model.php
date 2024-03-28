<?php
class selected_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_data() {
        $json_data = file_get_contents(APPPATH . 'data/data.json');
        return json_decode($json_data, true);
    }
    public function save_data($newData) {
        $data = $this->get_data();
        $data['selectedData'] = [];
        $data['selectedData'][] = $newData;
        $json_data = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents(APPPATH . 'data/data.json', $json_data);
        return true;
    }
    
}
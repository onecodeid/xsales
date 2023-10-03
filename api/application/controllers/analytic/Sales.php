<?php

class Sales extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('analytic/an_sales');
    }

    function mtd_projo()
    {
        $r = $this->an_sales->mtd_projo(
            date("Y-m-d", strtotime($this->sys_input['date'])));

        $this->sys_ok($r);
    }

    function mtd_projo_yearly()
    {
        $r = $this->an_sales->mtd_projo_yearly(2023);

        $this->sys_ok($r);
    }

    function search_period_year()
    {
        $rst = [];
        for ($i = $this->START_YEAR; $i<= date("Y"); $i++) {
            $rst[] = ['id'=>$i, 'text'=>$i, 'value'=>$i];
        }

        $this->sys_ok($rst);
    }
}

?>
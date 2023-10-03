<?php

class R_stat extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "r_report";
        $this->table_key = "R_ReportID";
    }

    // Statistik Omzet (qty) per product
    function Stat_sales_001($uid, $sdate, $edate, $type)
    {
        $r = $this->GetMultipleQueryResult("CALL `sp_stat_sales_001`('{$uid}', '{$sdate}', '{$edate}', '{$type}')", 1);
        return $r[0][0];
    }

    // Statistik Omzet (qty) per level
    function Stat_sales_002($uid, $sdate, $edate, $type, $level)
    {
        $r = $this->GetMultipleQueryResult("CALL `sp_stat_sales_002`('{$uid}', '{$sdate}', '{$edate}', '{$type}', '{$level}')", 2);

        if (isset($r[1]))
        {
            foreach ($r[1] as $k => $v)
                $r[1][$k]['pareto'] = json_decode($v['pareto']);
        }
        return $r;
    }

    // Statistik Omzet (qty) per province city
    function Stat_sales_003($uid, $sdate, $edate, $type)
    {
        $r = $this->GetMultipleQueryResult("CALL `sp_stat_sales_003`('{$uid}', '{$sdate}', '{$edate}', '{$type}')", 1);

        if (isset($r[0]))
        {
            foreach ($r[0] as $k => $v)
            {
                $r[0][$k]['omzet_cities'] = json_decode($v['omzet_cities']);
                $r[0][$k]['omzet_products'] = json_decode($v['omzet_products']);
            }
                
        }
        return $r;
    }

    // Statistik Omzet (qty) per province city
    function Stat_sales_005($uid, $sdate, $edate, $item_id)
    {
        $r = $this->GetMultipleQueryResult("CALL `sp_stat_sales_005`('{$sdate}', '{$edate}', '{$item_id}')", 2);
        $data = [];
// echo "CALL `sp_stat_sales_005`('{$uid}', '{$sdate}', '{$edate}', '{$item_id}')";
        if (isset($r[0]))
        {
            $data = ['province_max'=>$r[0][0]['total'], 'province'=>[]];
            foreach ($r[0] as $k => $v)
            {
                $data['province'][] = $v;
                $data['province'][$k]['city'] = [];
                $data['province'][$k]['city_max'] = 0;

                if (isset($r[1]))
                {
                    foreach ($r[1] as $kk => $vv)
                    {
                        if ($vv['M_ProvinceID'] == $v['M_ProvinceID'])
                        {
                            if ($data['province'][$k]['city_max']==0)
                                $data['province'][$k]['city_max'] = $vv['total'];
                            $data['province'][$k]['city'][] = $vv; 
                        }
                    }
                }
            }
        }

        return $data;
    }

    // Get Products Pareto per City
    function Stat_pareto_per_city($city_id, $sdate, $edate, $type)
    {
        $r = $this->db->query("SELECT fn_sales_pareto_by_city_date(?, ?, ?, ?) x", [$city_id, $sdate, $edate, $type])
                    ->row();
        $p = json_decode($r->x);
        return $p;
    }
}

?>
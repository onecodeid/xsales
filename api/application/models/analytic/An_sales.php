<?php

class An_sales extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "l_sales";
        $this->table_key = "L_SalesID";
    }

    function mtd_projo( $date )
    {
        $daymonth = date("t", strtotime($date));
        $sdate = date("Y-m-01", strtotime($date));
        $cday = date("j", strtotime($date));

//         SET @d = date(now());
// SET @sdate = DATE_SUB(@d,INTERVAL DAYOFMONTH(@d)-1 DAY);
// SET @edate = date(now());
// SET @daymonth = day(last_day(now()));
// -- SET @sdate = DATE_SUB(date(now()),INTERVAL DAYOFMONTH(date(now()))-1);
// select @d, @sdate, @daymonth;

        $q = "select *, target / {$daymonth} as mtd_1st, 
                target * {$cday} / {$daymonth} as mtd_current,
                ach * {$daymonth} / $cday as projo,
                (target - ach) / ({$daymonth} - {$cday}) as daily_need
                
                from (
                    select s_staffname staff_name,
                    sum(l_invoicegrandtotal-l_invoiceshipping-l_invoiceppn+l_invoicedp) ach, s_stafftargetmonth target, 
                    sum(l_invoicegrandtotal-l_invoiceshipping-l_invoiceppn+l_invoicedp) * 100 / s_stafftargetyear percentage
                    from l_invoice
                    join s_staff on l_invoices_staffid = s_staffid
                    where l_invoicedate between ? and ?
                    and l_invoiceisactive = 'Y'
                    group by l_invoices_staffid
                ) x
                ";
        $r = $this->db->query($q, [$sdate, $date])->result_array();

        return ['records'=>$r,'total'=>sizeof($r)];

        // -- select s_staffname staff_name,
        //             -- sum(L_SalesTotal) ach, s_stafftargetmonth target, sum(L_SalesTotal) * 100 / s_stafftargetyear percentage, SUM(L_SalesTotalHPP) hpp
        //             -- from l_sales
        //             -- join s_staff on l_saless_staffid = s_staffid
        //             -- where l_salesdate between ? and ?
        //             -- and l_salesisactive = 'Y'
        //             -- group by l_saless_staffid


// -- select s_staffid staff_id, s_staffcode staff_code, s_staffname staff_name, IFNULL(offer_cnt, 0) offer_cnt

// -- from s_staff
// -- join s_position on s_staffs_positionid = s_positionid and (s_positioncode = "POS.ADMIN" or s_positioncode = "POS.AVENGER")
// -- LEFT JOIN (

// -- select s_staffid staff_id, s_staffname staff_name, sum(L_LeadDetailB2B + L_LeadDetailB2C) offer_cnt
// -- from l_lead
// -- join l_leaddetail on l_leaddetaill_leadid = l_leadid and l_leaddetailisactive = "Y" and l_leaddetailtype = "P"
// -- join s_staff on l_leads_staffid = s_staffid
// -- where l_leaddate between @sdate and @edate
// -- and l_leadisactive = "Y"
// -- group by l_leads_staffid

// -- ) a on a.staff_id = s_staffid


// -- where s_staffisactive = "Y";

// END
    }

    function mtd_projo_yearly( $year )
    {
        $sdate = $year . '-01-01';
        $edate = $year . '-12-31';

//         SET @d = date(now());
// SET @sdate = DATE_SUB(@d,INTERVAL DAYOFMONTH(@d)-1 DAY);
// SET @edate = date(now());
// SET @daymonth = day(last_day(now()));
// -- SET @sdate = DATE_SUB(date(now()),INTERVAL DAYOFMONTH(date(now()))-1);
// select @d, @sdate, @daymonth;

        // find target
        $q = "SELECT m_targetyearlyamount x FROM m_targetyearly WHERE m_targetyearlyisactive = 'Y' and m_targetyearlyyear = ? and m_targetyearlys_staffid = 0";
        $tgt = $this->db->query($q, [$year])->row();

        $q = "select
                sum(l_invoicegrandtotal-l_invoiceshipping-l_invoiceppn+l_invoicedp-ifnull(retur_total, 0)) ach,
                month(l_invoicedate) invoice_month, M_TargetYearlyAmount total_target
                from l_invoice
                join m_targetyearly on m_targetyearlyisactive = 'Y' and m_targetyearlyyear = ? and m_targetyearlys_staffid = 0
                left join (
                    select l_returl_invoiceid iv_id, sum(l_returtotal) retur_total
                    from l_retur
                    where l_returisactive = 'Y'
                    group by l_returl_invoiceid
                ) rt on iv_id = l_invoiceid
                where l_invoicedate between ? and ?
                and l_invoiceisactive = 'Y'
                group by invoice_month
                order by invoice_month asc
                ";
        $r = $this->db->query($q, [$year, $sdate, $edate])->result_array();

        $months = [1,2,3,4,5,6,7,8,9,10,11,12];
        $rst = [];
        $monthly_target = $tgt->x / sizeof($months);
        $act_ach = 0;

        foreach ($months as $k => $v) {
            $x = ["month"=>date("F", strtotime($year.'-'.$v.'-01')), "ach"=>0];
             
            foreach ($r as $l => $w) {
                if ($w['invoice_month'] == $v) {
                    $x["ach"] = $w["ach"];
                    $act_ach += $w["ach"];
                    // $x["target"] = $w
                }

                $x["target"] = $monthly_target * $v;
                $x["target_pct"] = $monthly_target * $v * 100 / $tgt->x;
                $x["act_ach"] = $act_ach;
                $x["act_ach_pct"] = $act_ach * 100 / $tgt->x;
                $x["projo"] = ($act_ach / $v) * sizeof($months);
                $x["daily_need"] = sizeof($months) == $v ? 0 : ($tgt->x - $act_ach) / (sizeof($months) - $v);
            }
            $rst[] = $x;
        }

        return ['records'=>$rst,'total'=>sizeof($rst)];

        // -- select s_staffname staff_name,
        //             -- sum(L_SalesTotal) ach, s_stafftargetmonth target, sum(L_SalesTotal) * 100 / s_stafftargetyear percentage, SUM(L_SalesTotalHPP) hpp
        //             -- from l_sales
        //             -- join s_staff on l_saless_staffid = s_staffid
        //             -- where l_salesdate between ? and ?
        //             -- and l_salesisactive = 'Y'
        //             -- group by l_saless_staffid


// -- select s_staffid staff_id, s_staffcode staff_code, s_staffname staff_name, IFNULL(offer_cnt, 0) offer_cnt

// -- from s_staff
// -- join s_position on s_staffs_positionid = s_positionid and (s_positioncode = "POS.ADMIN" or s_positioncode = "POS.AVENGER")
// -- LEFT JOIN (

// -- select s_staffid staff_id, s_staffname staff_name, sum(L_LeadDetailB2B + L_LeadDetailB2C) offer_cnt
// -- from l_lead
// -- join l_leaddetail on l_leaddetaill_leadid = l_leadid and l_leaddetailisactive = "Y" and l_leaddetailtype = "P"
// -- join s_staff on l_leads_staffid = s_staffid
// -- where l_leaddate between @sdate and @edate
// -- and l_leadisactive = "Y"
// -- group by l_leads_staffid

// -- ) a on a.staff_id = s_staffid


// -- where s_staffisactive = "Y";

// END
    }
}
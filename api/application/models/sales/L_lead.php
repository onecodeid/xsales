<?php

class L_Lead extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "l_lead";
        $this->table_key = "L_LeadID";
    }

    function search( $d )
    {
        $dbiv = $this->db;
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];
        $staff = isset($d['staff_id'])?$d['staff_id']:0;

        $this->db->query("SET lc_time_names = 'id_ID'");
        $r = $dbiv->query(
                "SELECT L_LeadID lead_id, L_LeadNumber lead_number, L_LeadDate lead_date,
                L_LeadNote lead_note, S_StaffID staff_id, S_StaffName staff_name,
                DATE_FORMAT(L_LeadDate, '%W') lead_day,
                CONCAT('[', GROUP_CONCAT(JSON_OBJECT('d_id', l_leaddetailid, 'd_type', l_leaddetailtype,
                    'd_cid', l_leaddetailm_categoryid, 'd_lcid', l_leaddetailm_leadcategoryid, 'd_pid', l_leaddetailm_prospectid,
                    'd_b2b', l_leaddetailb2b, 'd_b2c', l_leaddetailb2c)),']') details
                FROM `{$this->table_name}`
                JOIN s_staff ON L_LeadS_StaffID = S_StaffID
                JOIN l_leaddetail ON L_LeadDetailL_LeadID = L_LeadID AND L_LeadDetailIsactive = 'Y'
                WHERE `L_LeadNumber` LIKE ?
                AND `L_LeadIsActive` = 'Y'
                AND ((L_LeadS_StaffID = ? AND ? > 0) OR ? = 0)
                AND L_LeadDate BETWEEN ? AND ?
                GROUP BY L_LeadID
                ORDER BY L_LeadDate DESC, L_LeadNumber DESC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'],
                    $staff, $staff, $staff,
                    $d['sdate'], $d['edate']]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['details'] = json_decode($v['details']);
                $r[$k]['values'] = json_decode("[]");
            }
            $l['records'] = $r;
        }

        $r = $dbiv->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
                JOIN s_staff ON L_LeadS_StaffID = S_StaffID
                WHERE `L_LeadNumber` LIKE ?
                AND `L_LeadIsActive` = 'Y'
                AND ((L_LeadS_StaffID = ? AND ? > 0) OR ? = 0)
                AND L_LeadDate BETWEEN ? AND ?", [$d['search'],
                    $staff, $staff, $staff,
                    $d['sdate'], $d['edate']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }


    function search_autocomplete( $d )
    {
        $d['limit'] = 200;
        $d['page'] = 1;
        $d['sdate'] = '2021-01-01';
        $d['edate'] = '2050-01-01';
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function save ( $d, $id = 0, $uid )
    {
        $r = $this->db->query("CALL sp_sales_lead_save_2(?,?,?,?)", [
        // $r = $this->db->query("CALL sp_sales_lead_save(?,?,?,?)", [
                        $id, 
                        $d['hdata'],
                        $d['jdata'],
                        $uid
                    ])
                    ->row();
                    
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function delete ($id, $uid = 0)
    {
        $r = $this->db->query("CALL sp_sales_lead_delete(?,?)", [$id, $uid])->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    // function get ($id)
    // {
    //     $this->db->query("SELECT
    //         JSON_OBJECT('M_CustomerID', M_CustomerID, 'M_CustomerM_CustomerLevelID', M_CustomerM_CustomerLevelID,
    //             'M_CustomerCode', M_CustomerCode, 'M_CustomerName', M_CustomerName,
    //             'M_CustomerAddress', M_CustomerAddress, 'M_CustomerM_KelurahanID', M_CustomerM_KelurahanID,
    //             'M_CustomerPhone', M_CustomerPhone, 'M_CityName', M_CityName,
    //             'M_ProvinceName', M_ProvinceName, 'kelurahan_id', kelurahan_id,
    //             'full_address', full_address, 'M_ProvinceID', M_ProvinceID,
    //             'M_CityID', M_CityID, 'M_DistrictID', M_DistrictID,
    //             'M_KelurahanID', M_KelurahanID, 'M_CustomerLevelID', M_CustomerLevelID,
    //             'M_CustomerLevelName', M_CustomerLevelName, 'address_kelurahan', address_kelurahan)

    //         FROM l_lead
    //         LEFT JOIN m_customer ON L_LeadM_CustomerID = M_CustomerID
    //     {
    //         "M_CustomerID":"473",
    //         "M_CustomerM_CustomerLevelID":"5",
    //         "M_CustomerCode":"Z090600087",
    //         "M_CustomerName":"Shanti Asih Karina Putri",
    //         "M_CustomerAddress":"Jl. Kemang II No.33C RT : 05/10",
    //         "M_CustomerM_KelurahanID":"9468",
    //         "M_CustomerPhone":"087875241545",
    //         "M_CustomerEmail":"",
    //         "M_CustomerPostCode":"16412",
    //         "M_CustomerNote":"",
    //         "M_CustomerAutoAccept":"N",
    //         "M_CustomerParentID":"240",
    //         "M_CustomerJoinDate":"2020-12-03",
    //         "M_CustomerBaseDate":"2020-12-03",
    //         "M_CustomerIsActive":"Y",
    //         "M_CustomerUserID":"13",
    //         "M_CustomerCreated":"2020-12-03 08:38:58",
    //         "M_CustomerLastUpdated":"2021-05-29 16:39:15",
    //         "M_CityName":"Depok",
    //         "M_ProvinceName":"Jawa Barat",
    //         "kelurahan_id":"9468",
    //         "full_address":"Sukmajaya » Sukmajaya » Depok » Jawa Barat",
    //         "M_ProvinceID":"9",
    //         "M_CityID":"69",
    //         "M_DistrictID":"984",
    //         "M_KelurahanID":"9468",
    //         "M_CustomerLevelID":"5",
    //         "M_CustomerLevelName":"End User",
    //         "address_kelurahan":"Sukmajaya Sukmajaya Depok Jawa Barat",
    //         "customer_note":[
    //         ],
    //         "orders":[
    //         ]
    //         }")
    // }
}

?>
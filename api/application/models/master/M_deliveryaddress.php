<?php

class M_deliveryaddress extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_deliveryaddress";
        $this->table_key = "M_DeliveryAddressID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        // SELECTED CUSTOMER
        $customer = isset($d['customer'])?$d['customer']:0;

        $r = $this->db->query(
                "SELECT M_DeliveryAddressID address_id, M_DeliveryAddressName address_name,
                M_DeliveryAddressDesc address_desc,
                IFNULL(M_KelurahanName, '') village_name,
                IFNULL(M_DistrictName, '') district_name,
                IFNULL(M_CityName, '') city_name,
                IFNULL(M_ProvinceName, '') province_name,
                M_ProvinceID province_id, M_CityID city_id, 
                M_DistrictID district_id, M_KelurahanID village_id,
                M_DeliveryAddressPostCode address_postcode,
                IFNULL(M_DeliveryAddressPhones, '[]') address_phone,
                M_DeliveryAddressPIC address_pic
                FROM `{$this->table_name}`
                LEFT JOIN m_kelurahan ON M_DeliveryAddressM_KelurahanID = M_KelurahanID
                LEFT JOIN m_district ON M_DeliveryAddressM_DistrictID = M_DistrictID
                LEFT JOIN m_city ON M_DeliveryAddressM_CityID = M_CityID
                LEFT JOIN m_province ON M_CityM_ProvinceID = M_ProvinceID
                WHERE (`M_DeliveryAddressName` LIKE ?)
                AND `M_DeliveryAddressIsActive` = 'Y'
                AND ((M_DeliveryAddressM_CustomerID = ? AND ? <> 0) OR ? = 0)
                ORDER BY M_DeliveryAddressName ASC
                LIMIT {$limit} OFFSET {$offset}", [$d['search'], $customer, $customer, $customer]);
        if ($r)
        {
            $r = $r->result_array();
            foreach ($r as $k => $v)
            {
                $r[$k]['address_phone'] = json_decode($v['address_phone']);
            }
                
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            LEFT JOIN m_kelurahan ON M_DeliveryAddressM_KelurahanID = M_KelurahanID
                LEFT JOIN m_district ON M_DeliveryAddressM_DistrictID = M_DistrictID
                LEFT JOIN m_city ON M_DeliveryAddressM_CityID = M_CityID
                LEFT JOIN m_province ON M_CityM_ProvinceID = M_ProvinceID
            WHERE (`M_DeliveryAddressName` LIKE ?)
            AND `M_DeliveryAddressIsActive` = 'Y'
            AND ((M_DeliveryAddressM_CustomerID = ? AND ? <> 0) OR ? = 0)
            ", [$d['search'], $customer, $customer, $customer]);

        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->query("CALL sp_master_delivery_address_save(?,?,?,?)", [
            $d['customer_id'], 0, $d['jdata'], $d['user_id']])->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        if ($r->status == "OK")
        {
            $r->data = json_decode($r->data);
        }

        return $r;
    }

    function edit ( $d )
    {
        // $r = $this->db->query("CALL sp_master_address_save(?,?,?,?)", [
        //     $d['address_id'], json_encode([
        //         'name'=>$d['address_name'],
        //         'address'=>$d['address_address'],
        //         'phone'=>$d['address_phone'],
        //         'phones'=>$d['address_phones'],
        //         'email'=>$d['address_email'],
        //         'postcode'=>$d['address_postcode'],
        //         'note'=>$d['address_note'],
        //         'postcode'=>$d['address_postcode'],
        //         'pic_name'=>$d['address_pic_name'],
        //         'pic_phone'=>$d['address_pic_phone'],
        //         'npwp'=>$d['address_npwp'],
        //         'prospect'=>isset($d['address_prospect'])?$d['address_prospect']:'N',
        //         'company'=>$d['address_type'],
        //         'city'=>isset($d['address_city_id'])?$d['address_city_id']:0,
        //         'district'=>isset($d['address_district_id'])?$d['address_district_id']:0,
        //         'kelurahan'=>isset($d['address_kelurahan_id'])?$d['address_kelurahan_id']:0,
        //         'staff'=>isset($d['address_staff'])?$d['address_staff']:0
        //     ]), isset($d['bdata'])?$d['bdata']:'[]', 0
        // ])->row();
        // $this->clean_mysqli_connection($this->db->conn_id);

        // if ($r->status == "OK")
        // {
        //     $r->data = json_decode($r->data);
        //     $this->set_index($r->data->address_id);
        // }

        // return $r;
    }

    function del ($id, $uid = 0)
    {
        $r = $this->db->query("CALL sp_master_address_delete(?, ?)", [$id, $uid])
                    ->row();
        $this->clean_mysqli_connection($this->db->conn_id);

        return $r;
    }

    function search_autocomplete( $d )
    {
        $d['limit'] = 25;
        $d['page'] = 1;
        $r = $this->search( $d );
        
        return $r['records'];
    }

    function set_index($id = 0)
    {
        if ($id > 0)
            $this->db->where('M_DeliveryAddressID', $id);

        $r = $this->db->select('M_DeliveryAddressID id, M_DeliveryAddressNAme name', false)
                    ->where('M_DeliveryAddressIsACtive', 'Y')
                    ->get($this->table_name)
                    ->result_array();
        foreach ($r as $k => $v)
        {
            $e = explode(" ", $v['name']);
            $f = [];
            foreach ($e as $l => $w)
                if ($w != '')
                    $f[] = '['.metaphone($w).']';
            sort($f);
            $this->db->where('M_DeliveryAddressID', $v['id'])
                    ->set('M_DeliveryAddressIndex', join('', $f))
                    ->update($this->table_name);
        }
    }

    function search_similar($p)
    {
        $e = explode(" ", $p);
        $f = [];
        $g = [];
        foreach ($e as $k => $v)
        {
            $f[] = metaphone($v);
            $g[] = '['.metaphone($v).']';
        }
        
        $r = $this->db->query("CALL sp_master_address_search_index(?,?,?)", [join('',$g), $f[0], isset($f[1])?$f[1]:''])
                ->result_array();
        $this->clean_mysqli_connection($this->db->conn_id);

        if ($r)
            return $r;
        return [];
    }

    function _tmp_idx()
    {
        $r = $this->db->where('M_DeliveryAddressIsActive', 'Y')
                    ->get($this->table_name)
                    ->result_array();
        foreach($r as $k => $v)
        {
            preg_match_all("/\[[A-Z]+\]/", $v['M_DeliveryAddressIndex'], $e);
            foreach ($e[0] as $l => $w)
            {
                echo $w."<br>";
                $this->db->query("insert into _tmp_cust_idx(cid, idx) values(?,?)", [$v['M_DeliveryAddressID'], $w]);
            }
                
        }
    }
}

?>
<?php

class Rajaongkir extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('system/s_menu');
    }

    function index()
    {
        return;
    }

    function sync_province()
    {
        $min_id = 1;
        $max_id = 34;

        for ($i=$min_id; $i<=$max_id; $i++)
        {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->RO_URL."province?id=".$i,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "key: ".$this->RO_KEY
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
            echo "cURL Error #:" . $err;
            } else {
                $d = json_decode($response);
                
                $this->db->where('M_ProvinceName', $d->rajaongkir->results->province)
                        ->set('M_ProvinceROID', $d->rajaongkir->results->province_id)
                        ->update('m_province');
            }
        }
    }

    function sync_city()
    {
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->RO_URL."city?province=9",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: ".$this->RO_KEY
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        print_r($response);
        // if ($err) {
        // echo "cURL Error #:" . $err;
        // } else {
        //     $d = json_decode($response);
            
        //     foreach ($d->rajaongkir->results as $k => $v)
        //     {
        //         $this->db->query("INSERT INTO _tmp_city_ro(city_id, ro_id)
        //                         SELECT M_CityID, ?
        //                         FROM m_city
        //                         JOIN m_province ON M_ProvinceROID = ? AND M_ProvinceIsActive = 'Y' AND M_ProvinceID = M_CityM_ProvinceID
        //                         WHERE M_CityName = ?", [$v->city_id, $v->province_id, $v->city_name]);

        //         $this->db->query("UPDATE m_city
        //                         JOIN m_province ON M_ProvinceROID = ? AND M_ProvinceIsActive = 'Y'
        //                         SET M_CityROID = ?
        //                         WHERE M_CityName = ?", [$v->province_id, $v->city_id, $v->city_name]);
        //     }
        // }
        
    }

    function sync_district()
    {
        // $x = $this->db->limit(40)->offset(510)->get('m_city')->result_array();
        $x = $this->db->where('M_CityID', 64)->get('m_city')->result_array();
        // $x = $this->db->join('m_city', 'M_CityID=M_DistrictM_CityID')
        //             ->join('_tmp_city_ro', 'city_id=M_CityID')
        //         ->where('M_DistrictROID', 0)->get('m_district')->result_array();
        
        foreach ($x as $y => $z)
        {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->RO_URL."subdistrict?city=78",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "key: ".$this->RO_KEY
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);
            // print_r($response);
            if ($err) {
            echo "cURL Error #:" . $err;
            } else {
                $d = json_decode($response);
            
                foreach ($d->rajaongkir->results as $k => $v)
                {
                    echo $v->subdistrict_name.' | ' . $v->subdistrict_id .'<br>';
                    // $this->db->query("UPDATE m_district
                    //                 SET M_DistrictROID = ?
                    //                 WHERE M_DistrictName = ?
                    //                 AND M_DistrictM_CityID = ?", [$v->subdistrict_id, $v->subdistrict_name, $z['M_CityID']]);
                    // echo $this->db->last_query();
                }
            }
            
        }
        
        
    }
}

?>
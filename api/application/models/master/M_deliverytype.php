<?php

class M_deliverytype extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_deliverytype";
        $this->table_key = "M_DeliveryTypeID";
    }

    function search( $d )
    {
        $l = ['records'=>[], 'total'=>0];

        $r = $this->db->query(
                "SELECT M_DeliveryTypeID deliverytype_id, M_DeliveryTypeName deliverytype_name, M_DeliveryTypeCode deliverytype_code
                FROM `{$this->table_name}`
                WHERE `M_DeliveryTypeName` LIKE ?
                AND `M_DeliveryTypeIsActive` = 'Y'
                ORDER BY M_DeliveryTypeID", [$d['search']]);
        if ($r)
        {
            $r = $r->result_array();
            $l['records'] = $r;
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE `M_DeliveryTypeName` LIKE ?
            AND `M_DeliveryTypeIsActive` = 'Y'", [$d['search']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_DeliveryTypeName', $d['deliverytype_name'])
                    ->set('M_DeliveryTypeCode', $d['deliverytype_code']);
                    // ->set('M_DeliveryTypeUserID', $d['user_id']);
        if (isset($d['deliverytype_id']))
        {
            $this->db->where('M_DeliveryTypeID', $d['deliverytype_id'])
                ->update( $this->table_name );
            $id = $d['deliverytype_id'];
        }
        else
        {
            $this->db->insert( $this->table_name );
            $id = $this->db->insert_id();
        }

        if ($r)
        {
            return (object) ["status"=>"OK", "data"=>$id];
        }

        return (object) ["status"=>"ERR"];
    }

    function del ($id)
    {
        $this->db->set('M_DeliveryTypeIsActive', 'N')
                ->where('M_DeliveryTypeID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>
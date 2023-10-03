<?php

class M_asset extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "m_asset";
        $this->table_key = "M_AssetID";
    }

    function search( $d )
    {
        $limit = isset($d['limit']) ? $d['limit'] : 10;
        $offset = ($d['page'] - 1) * $limit;
        $l = ['records'=>[], 'total'=>0, 'total_page'=>1];

        $r = $this->db->query(
                "SELECT M_AssetID asset_id, M_AssetCode asset_code, M_AssetName asset_name,
                    M_AssetDescription asset_description, M_AssetM_AccountID asset_account_id,
                    M_AssetAcqDate asset_acq_date,
                    M_AssetAcqCost asset_acq_cost,
                    M_AssetAcqM_AccountID asset_acq_account,
                    M_AssetDepreciable asset_depreciable,
                    M_AssetM_DepMethodID asset_dep_method,
                    M_AssetDepreciableTime asset_dep_time,
                    M_AssetDepreciableRate asset_dep_rate,
                    M_AssetDepreciableM_AccountID asset_dep_account,
                    M_AssetDepreciableAccumulatedM_AccountID asset_dep_accumulated_account,
                    M_AssetDepreciableAccumulatedValue asset_dep_value,
                    IFNULL(M_DepMethodName, '') dep_method,
                    IFNULL(M_AccountCode, '') account_code,
                    IFNULL(M_AccountName, '') account_name
                FROM `{$this->table_name}`
                LEFT JOIN m_depmethod ON M_AssetM_DepMethodID = M_DepMethodID
                LEFT JOIN m_account ON M_AssetM_AccountID = M_AccountID
                WHERE (`M_AssetName` LIKE ? OR `M_AssetCode` LIKE ?)
                AND `M_AssetIsActive` = 'Y'
                LIMIT {$limit} OFFSET {$offset}", [$d['asset_name'], $d['asset_name']]);
        if ($r)
        {
            $l['records'] = $r->result_array();
        }

        $r = $this->db->query(
            "SELECT count(`{$this->table_key}`) n
            FROM `{$this->table_name}`
            WHERE (`M_AssetName` LIKE ? OR `M_AssetCode` LIKE ?)
            AND `M_AssetIsActive` = 'Y'", [$d['asset_name'], $d['asset_name']]);
        if ($r)
        {
            $l['total'] = $r->row()->n;
            $l['total_page'] = ceil($r->row()->n / $limit);
        }
            
        return $l;
    }

    function save ( $d )
    {
        $r = $this->db->set('M_AssetName', $d['asset_name'])
                    ->set('M_AssetCode', $d['asset_code'])
                    ->set('M_AssetDescription', $d['asset_description'])
                    ->set('M_AssetM_AccountID', $d['asset_account_id'])
                    ->set('M_AssetAcqDate', $d['asset_acq_date'])
                    ->set('M_AssetAcqCost', $d['asset_acq_cost'])
                    ->set('M_AssetAcqM_AccountID', $d['asset_acq_account'])
                    ->set('M_AssetDepreciable', $d['asset_depreciable'])
                    ->set('M_AssetM_DepMethodID', $d['asset_dep_method'])
                    ->set('M_AssetDepreciableTime', $d['asset_dep_time'])
                    ->set('M_AssetDepreciableRate', $d['asset_dep_rate'])
                    ->set('M_AssetDepreciableM_AccountID', $d['asset_dep_account'])
                    ->set('M_AssetDepreciableAccumulatedM_AccountID', $d['asset_dep_accumulated_account'])
                    ->set('M_AssetDepreciableAccumulatedValue', isset($d['asset_dep_value'])?$d['asset_dep_value']:0);
                    // ->set('M_AssetUserID', $d['user_id']);
        if (isset($d['asset_id']))
        {
            $this->db->where('M_AssetID', $d['asset_id'])
                ->update( $this->table_name );
            $id = $d['asset_id'];
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
        $this->db->set('M_AssetIsActive', 'N')
                ->where('M_AssetID', $this->sys_input['id'])
                ->update($this->table_name);

        return true;
    }
}

?>
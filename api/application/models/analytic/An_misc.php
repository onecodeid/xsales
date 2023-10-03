<?php

class An_misc extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = "an_misc";
        $this->table_key = "An_MiscID";
    }

    function save_by_code($code, $d) {
        $attr1 = isset($d['attr1'])?$d['attr1']:0;
        $attr2 = isset($d['attr2'])?$d['attr2']:0;
        $r = $this->db->query("SELECT An_MiscID id FROM an_misc WHERE An_MiscCode = ? AND An_MiscAttr1 = ? AND An_MiscAttr2 = ? AND An_MiscIsActive = 'Y' limit 1", [$code, $attr1, $attr2])->row();

        $this->db->set('An_MiscDate', isset($d['date'])?$d['date']:date('Y-m-d'))
                ->set('An_MiscAttr1', $attr1)
                ->set('An_MiscAttr2', $attr2)
                ->set('An_MiscDesc', isset($d['desc'])?$d['desc']:'');
        if ($r && !!$r->id) {
            $id = $r->id;
            $x = $this->db->where($this->table_key, $id)->update($this->table_name);
        } else {
            $x = $this->db->set('An_MiscCode', $code)->insert($this->table_name);
            $id = $this->db->insert_id();
        }

        return $id;
    }

    function search($d, $rst = 'array') {
        $this->db->select('An_MiscID misc_id, An_MiscCode misc_code, An_MiscDesc misc_desc', false)
                ->wherE('An_MiscIsActive', 'Y');
        foreach ($d as $k => $v)
            $this->db->where($k, $v);
            
        $r = $this->db->get($this->table_name);
        $r = $rst == 'array' ? $r->result_array() : $r->row();

        return $r;
    }
}
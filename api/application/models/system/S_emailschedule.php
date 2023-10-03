<?php

class S_emailschedule extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        $this->table_name = 's_email_schedule';
        $this->primary_key = 'S_EmailScheduleID';
    }

    function get_one($type)
    {
        $r = $this->db->query("SELECT *
                            FROM `{$this->table_name}`
                            WHERE S_EmailScheduleType = ?
                            AND S_EmailScheduleSent = 'N'
                            AND S_EmailScheduleIsActive = 'Y'
                            LIMIT 1", $type);
        if ($r->num_rows() > 0)
        {
            $r = $r->row();
            return $r;
        }
        return false;
    }

    function get_unsent($min_date = '1971-01-01', $limit = 2)
    {
        $r = $this->db->query("SELECT *
                            FROM `{$this->table_name}`
                            WHERE S_EmailScheduleSent = 'N'
                            AND S_EmailScheduleIsActive = 'Y'
                            AND S_EmailScheduleCreated > ?
                            LIMIT {$limit}", date('Y-m-d 00:00:00', strtotime($min_date)));
        if ($r->num_rows() > 0)
        {
            $r = $r->result_array();
            return $r;
        }
        return false;
    }

    function get_by($type, $refid)
    {
        $r = $this->db->query("SELECT *
                            FROM `{$this->table_name}`
                            WHERE S_EmailScheduleType = ?
                            AND S_EmailScheduleSent = 'N'
                            AND S_EmailScheduleIsActive = 'Y'
                            AND S_EmailScheduleReffID = ?
                            ORDER BY S_EmailScheduleID DESC
                            LIMIT 1", [$type, $refid]);
        if ($r->num_rows() > 0)
        {
            $r = $r->row();
            return $r;
        }
        return false;
    }

    function sent($id)
    {
        $this->db->set('S_EmailScheduleSent', 'Y')
                ->set('S_EmailScheduleSentDate', date('Y-m-d H:i:s'))
                ->where('S_EmailScheduleID', $id)
                ->update($this->table_name);
        return true;
    }

    function save($d)
    {
        $r = $this->db->set('S_EmailScheduleType', $d['type'])
                        ->set('S_EmailScheduleAddress', $d['address'])
                        ->set('S_EmailScheduleSubject', $d['subject'])
                        ->set('S_EmailScheduleContent', $d['content'])
                        ->set('S_EmailScheduleReffID', isset($d['reff_id'])?$d['reff_id']:0)
                        ->insert($this->table_name);
        if ($r)
        {
            return $this->db->insert_id();
        }

        return false;
    }
}
?>

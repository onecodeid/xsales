<?php

class Journal extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('trans/t_journal');
    }

    function search()
    {
        $inp = $this->sys_input;
        $prm = ['search'=>'%'.$inp['search'].'%', 
                'page'=>$inp['page'], 
                'sdate'=>$inp['sdate'], 
                'edate'=>$inp['edate']];

        if (isset($inp['filter']))
            $prm['filter'] = $inp['filter'];
        if (isset($inp['jtype']))
            $prm['jtype'] = $inp['jtype'];

        $r = $this->t_journal->search($prm);
        $this->sys_ok($r);
    }

    function search_id()
    {
        $inp = $this->sys_input;
        $r = $this->t_journal->search_id($inp['journal_id']);
        $this->sys_ok($r);
    }

    function search_cashbank()
    {
        $inp = $this->sys_input;
        $r = $this->t_journal->search_cashbank(
            ['search'=>'%'.$inp['search'].'%', 
            'page'=>$inp['page'], 
            'sdate'=>$inp['sdate'], 
            'edate'=>$inp['edate'],
            'account_id'=>$inp['account_id']]);

        $this->load->model('finance/f_payment2');
        $this->load->model('admin/a_customer');
        
        foreach ($r['records'] as $k => $v)
        {
            // print_r($v);
            if ($v['journal_type'] == 'J.11')
            {
                
                $x = $this->f_payment2->search(['journal_id'=>$v['journal_id'],'sdate'=>$v['journal_date'],'edate'=>$v['journal_date']]);                
                $r['records'][$k]['payment'] = $x['records'][0];
            }
        }
        $this->sys_ok($r);
    }

    function search_by_account()
    {
        $inp = $this->sys_input;
        $prm = ['search'=>'%'.$inp['search'].'%', 
                'page'=>$inp['page'], 
                'sdate'=>$inp['sdate'], 
                'edate'=>$inp['edate'],
                'account_id'=>$inp['account_id']];

        // if (isset($inp['filter']))
        //     $prm['filter'] = $inp['filter'];
        // if (isset($inp['jtype']))
        //     $prm['jtype'] = $inp['jtype'];

        $r = $this->t_journal->search_by_account($prm);
        $this->sys_ok($r);
    }

    function save()
    {
        // PRE CHECK
        $jdata = json_decode($this->sys_input['jdata']);
        $total = [0, 0];
        foreach ($jdata as $k => $v)
        {
            $total[0] += $v->debit;
            $total[1] += $v->credit;
        }

        if ($total[0] < 1 || $total[1] < 1 || round($total[0], 3) != round($total[1], 3) )
        {
            // var_dump($total[1] < 0); var_dump($total[1] < 1); var_dump($total[0] != $total[1]); var_dump($total[0]); var_dump($total[1]); var_dump(abs(($total[0]-$total[1])/$total[1]));
            $this->sys_error("Silahkan cek kembali nominal debit / credit yang anda masukkan :)");
            return;
        }
        // END OF PRE CHECK

        if (isset($this->sys_input['journal_id']))
            $r = $this->t_journal->save( $this->sys_input, $this->sys_input['journal_id'] );
        else
            $r = $this->t_journal->save( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function del()
    {
        $r = $this->t_journal->del( $this->sys_input['journal_id'] );
        if ($r->status == "OK")
            $this->sys_ok($r);
        else
            $this->sys_error($r);
    }

    function post()
    {
        $r = $this->t_journal->post( $this->sys_input['id'] );
        if ($r->status == "OK")
            $this->sys_ok($r);
        else
            $this->sys_error($r);
    }

    function cash_receive()
    {
        if (isset($this->sys_input['journal_id']))
            $r = $this->t_journal->cash_receive2( $this->sys_input, $this->sys_input['payment_id'] );
        else
            $r = $this->t_journal->cash_receive2( $this->sys_input );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function cash_delete()
    {
        $r = $this->t_journal->cash_delete( $this->sys_input['journal_id'], 
                $this->sys_user['user_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }

    function get_detail()
    {
        $r = $this->t_journal->get_detail( $this->sys_input['journal_id'] );
        
        if ($r->status == "OK")
            $this->sys_ok($r->data);
        else
            $this->sys_error($r->message);
    }
}

?>
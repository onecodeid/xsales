
<?php

class Stock extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('inventory/i_stock');
    }

    function index()
    {
        return;
    }

    function search_item_w_stock()
    {
        $d = [
            'search'=>'%'.(isset($this->sys_input['search'])?$this->sys_input['search']:'').'%',
            'warehouse'=>isset($this->sys_input['warehouse'])?$this->sys_input['warehouse']:0,
            'to'=>isset($this->sys_input['warehouse_to'])?$this->sys_input['warehouse_to']:0
        ];
        if (isset($this->sys_input['available'])) $d['available'] = true;
        
        $r = $this->i_stock->search_item_w_stock( $d );
        $this->sys_ok($r);
    }

    function search_by_item()
    {        
        $r = $this->i_stock->search_by_item( $this->sys_input['item_id'] );
        $this->sys_ok($r);
    }

    function search_by_items()
    {        
        $r = $this->i_stock->search_by_items( join(',', $this->sys_input['item_ids']), $this->sys_input['warehouse_id'] );
        $this->sys_ok($r);
    }

    function histories()
    {
        $r = $this->i_stock->histories($this->sys_input['item_id']);

        $this->sys_ok($r);
    }

    function history_detail()
    {
        $r = $this->i_stock->history_detail($this->sys_input['log_id'], $this->sys_input['log_code']);

        $this->sys_ok($r);
    }
    // function save()
    // {
    //     $r = $this->m_customer->save( $this->sys_input );
    //     echo json_encode($r);
    // }

    // function del()
    // {
    //     $r = $this->m_customer->del( $this->sys_input );
    //     $this->sys_ok($r);
    // }
}

?>
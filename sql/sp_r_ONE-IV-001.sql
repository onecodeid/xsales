BEGIN 

select m_itemid item_id, m_itemcode item_code, m_itemname item_name, m_unitname unit_name,
m_warehouseid warehouse_id, m_warehousename warehouse_name,  m_warehousecode warehouse_code,
i_stockqty stock_qty, m_itemminstock stock_min
from m_item
join m_unit on m_itemm_unitid = m_unitid
left join i_stock on i_stockm_itemid = m_itemid and i_Stockisactive = "Y"
left join m_warehouse on i_stockm_warehouseid = m_warehouseid
where m_itemisactive = "Y"
order by m_itemname, m_warehouseid;

select m_warehouseid warehouse_id, m_warehousecode warehouse_code, m_warehousename warehouse_name, m_warehouseshortname warehouse_short_name
from m_warehouse where m_warehouseisactive = "Y" order by m_warehousename;

END
SELECT m_itemid item_id, m_itemcode item_code, m_itemname item_name, m_unitname unit_name,
m_warehouseid warehouse_id, m_warehousename warehouse_name,  m_warehousecode warehouse_code,
IFNULL(log_after_qty, 0) stock_qty, m_itemminstock stock_min, m_itemdefaulthpp item_hpp,
M_CategoryName category_name

FROM m_item
join m_unit on m_itemm_unitid = m_unitid
join m_category ON M_ItemM_CategoryID = M_CategoryID
JOIN (
    SELECT Log_StockM_ItemID log_item_id, Log_StockM_WarehouseID log_warehouse_id, SUM(Log_StockAfterQty) log_after_qty
    FROM xsales_log.log_stock
    WHERE Log_StockIndex IN (
        SELECT MAX(Log_StockIndex)
        FROM one_account_aw_log.log_stock
        WHERE Log_StockIsActive = "Y"
        AND Log_StockDate <= ? AND ((Log_StockM_WarehouseID = ? AND ? <> 0) OR ? = 0)
        GROUP BY Log_StockM_ItemID, Log_StockM_WarehouseID
    ) 
    AND Log_StockAfterQty > 0
    GROUP BY Log_StockM_ItemID) lg ON log_item_id = M_ItemID
LEFT join m_warehouse on log_warehouse_id = m_warehouseid AND M_WarehouseID = ?
where m_itemisactive = "Y" and (M_ITemName LIKE ? OR M_ITemCode LIKE ?)
order by m_itemname, m_warehouseid;

-- select m_warehouseid warehouse_id, m_warehousecode warehouse_code, m_warehousename warehouse_name, m_warehouseshortname warehouse_short_name
-- from m_warehouse where m_warehouseisactive = "Y" and m_warehouseid = warehouseid order by m_warehousename;
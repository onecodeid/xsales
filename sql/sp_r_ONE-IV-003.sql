DROP PROCEDURE `sp_r_ONE-IV-003`;
DELIMITER ;;
CREATE PROCEDURE `sp_r_ONE-IV-003` (IN `warehouseid` integer, IN `logdate` DATE)
BEGIN 

-- select m_itemid item_id, m_itemcode item_code, m_itemname item_name, m_unitname unit_name,
-- m_warehouseid warehouse_id, m_warehousename warehouse_name,  m_warehousecode warehouse_code,
-- i_stockqty stock_qty, m_itemminstock stock_min, m_itemdefaulthpp item_hpp
-- from m_item
-- join m_unit on m_itemm_unitid = m_unitid
-- join i_stock on i_stockm_itemid = m_itemid and i_Stockisactive = "Y" and i_stockqty > 0
--     and i_Stockm_warehouseid = warehouseid
-- join m_warehouse on i_stockm_warehouseid = m_warehouseid
-- where m_itemisactive = "Y"
-- order by m_itemname, m_warehouseid;

SELECT m_itemid item_id, m_itemcode item_code, m_itemname item_name, m_unitname unit_name,
m_warehouseid warehouse_id, m_warehousename warehouse_name,  m_warehousecode warehouse_code,
IFNULL(log_after_qty, 0) stock_qty, m_itemminstock stock_min, m_itemdefaulthpp item_hpp

FROM m_item
join m_unit on m_itemm_unitid = m_unitid
JOIN (
    SELECT Log_StockM_ItemID log_item_id, Log_StockM_WarehouseID log_warehouse_id, Log_StockAfterQty log_after_qty
    FROM one_account_aw_log.log_stock
    WHERE Log_StockIndex IN (
        SELECT MAX(Log_StockIndex)
        FROM one_account_aw_log.log_stock
        WHERE Log_StockIsActive = "Y"
        AND Log_StockDate <= logdate AND Log_StockM_WarehouseID = warehouseid
        GROUP BY Log_StockM_ItemID, Log_StockM_WarehouseID
    ) 
    AND Log_StockAfterQty > 0) lg ON log_item_id = M_ItemID
LEFT join m_warehouse on log_warehouse_id = m_warehouseid AND M_WarehouseID = warehouseid
where m_itemisactive = "Y"
order by m_itemname, m_warehouseid;

select m_warehouseid warehouse_id, m_warehousecode warehouse_code, m_warehousename warehouse_name, m_warehouseshortname warehouse_short_name
from m_warehouse where m_warehouseisactive = "Y" and m_warehouseid = warehouseid order by m_warehousename;

END;;
DELIMITER ;
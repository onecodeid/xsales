SELECT *
FROM ( SELECT m_itemid item_id, m_itemcode item_code, m_itemname item_name, m_unitname unit_name,
(ifnull(omzet_qty, 0) + ifnull(assembly_qty, 0)) omzet_qty, 
(ifnull(omzet_freq, 0) + ifnull(assembly_cnt, 0)) omzet_freq, 
ifnull(assembly_cnt, 0) assembly_cnt,
ifnull(log_after_qty, 0) item_stock

FROM m_item
join m_unit on m_itemm_unitid = m_unitid
join m_category ON M_ItemM_CategoryID = M_CategoryID
LEFT JOIN (
    SELECT Log_StockM_ItemID log_item_id, Log_StockM_WarehouseID log_warehouse_id, SUM(Log_StockAfterQty) log_after_qty
    FROM one_account_aw_log.log_stock
    WHERE Log_StockIndex IN (
        SELECT MAX(Log_StockIndex)
        FROM one_account_aw_log.log_stock
        WHERE Log_StockIsActive = "Y"
        AND Log_StockDate <= ? AND ((Log_StockM_WarehouseID = ? AND ? <> 0) OR ? = 0)
        GROUP BY Log_StockM_ItemID, Log_StockM_WarehouseID
    ) 
    AND Log_StockAfterQty > 0
    GROUP BY Log_StockM_ItemID) lg ON log_item_id = M_ItemID

LEFT JOIN (
    SELECT M_ItemID p_item_id, M_ItemName item_name, stock_min as p_item_min_stock,
        COUNT(DISTINCT L_DeliveryID) omzet_freq, SUM(L_DeliveryDetailQty) omzet_qty,

        IFNULL(stock_qty, 0) stock_qty
        
        FROM l_deliverydetail
        JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
            AND L_DeliveryDate BETWEEN ? AND ?
            AND L_DeliveryConfirm = 'Y'
            AND L_DeliveryIsActive = 'Y'
        JOIN m_warehouse ON L_DeliveryM_WarehouseID = M_WarehouseID
        JOIN m_item ON L_DeliveryDetailA_ItemID = M_ItemID

        LEFT JOIN (
            SELECT SUM(I_StockQty) stock_qty, I_StockM_ItemID stock_item_id,
                SUM(I_StockMinQty) stock_min
            FROM i_stock
            WHERE ((I_StockM_WarehouseID = ? AND ? <> 0) OR ? = 0)
                AND I_StockIsActive = 'Y'
            GROUP BY I_StockM_ItemID
        ) stock ON stock.stock_item_id = M_ItemID

        WHERE L_DeliveryDetailIsActive = 'Y'
            AND ((L_DeliveryM_WarehouseID = ? AND ? <> 0) OR ? = 0)
        GROUP BY L_DeliveryDetailA_ItemID
        ORDER BY M_ItemName
) p ON p_item_id = M_ItemID

LEFT JOIN (
    SELECT COUNT(DISTINCT I_AssemblyID) assembly_cnt, SUM(I_AssemblyDetailQty) assembly_qty, I_AssemblyDetailM_ItemID assembly_item
    FROM i_assemblydetail
    JOIN i_assembly ON I_AssemblyDate BETWEEN ? AND ? AND I_AssemblyIsActive = "Y"
    WHERE I_AssemblyDetailIsActive = "Y"
    AND ((I_AssemblyM_WarehouseID = ? AND ? <> 0) OR ? = 0)
    GROUP BY I_AssemblyDetailM_ItemID
) assmb ON assembly_item = M_ItemID

where m_itemisactive = "Y" and (M_ITemName LIKE ? OR M_ITemCode LIKE ?)
and (log_after_qty is not null or omzet_freq is not null)
order by m_itemname) xyz order by omzet_freq desc, item_name asc
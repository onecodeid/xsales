SELECT M_ItemSlowID slow_id, M_WarehouseName warehouse_name, M_ItemCode item_code, M_ItemName item_name, IFNULL(I_StockQty, 0) stock_qty,
    M_UnitName unit_name, M_ItemDefaultHPP item_hpp
FROM m_itemslow
JOIN m_item ON M_ItemSlowM_ItemID = M_ItemID
JOIN m_warehouse ON M_ItemSlowM_WarehouseID = M_WarehouseID
JOIN m_unit ON M_ItemM_UnitID = M_UnitID
LEFT JOIN i_stock ON M_ItemID = I_StockM_ItemID AND M_WarehouseID = I_StockM_WarehouseID
WHERE M_ItemSlowIsActive = "Y" AND M_ItemSlowValue = "Y"
    AND ((M_WarehouseID = ? AND ? <> 0) OR ? = 0)
    AND (M_ItemName LIKE ? OR M_ItemCode LIKE ?)
ORDER BY M_WarehouseName, M_ItemName
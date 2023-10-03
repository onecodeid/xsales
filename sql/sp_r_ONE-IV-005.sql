BEGIN

SELECT Log_StockID stock_id, Log_StockRefID ref_id, Log_StockRefNumber ref_number,
M_ItemID item_id, M_ItemName item_name, M_UnitID unit_id, M_UnitName unit_name,
IFNULL(M_CustomerID, 0) customer_id, IFNULL(M_CustomerName, '') customer_name,
IFNULL(M_VendorID, 0) vendor_id, IFNULL(M_VendorName, '') vendor_name,
Log_StockBeforeQty stock_before_qty, Log_StockQty stock_qty, Log_StockAfterQty stock_after_qty,

Log_TypeCode type_code, Log_TypeText type_text


FROM one_account_aw_log.log_stock
JOIN m_item ON Log_StockM_ItemID = M_ItemID
JOIN m_unit ON M_ItemM_UnitID = M_UnitID
LEFT JOIn m_vendor ON Log_StockM_SupplierID = M_VendorID AND Log_StockM_SupplierID <> 0
LEFT JOIN m_customer ON Log_StockM_CustomerID = M_CustomerID AND Log_StockM_CustomerID <> 0
JOIN m_warehouse wa ON Log_StockM_WarehouseID = wa.M_WarehouseID
LEFT JOIN m_warehouse wb ON Log_StockFromToM_WarehouseID = wb.M_WarehouseID
LEFT JOIN one_account_aw_log.log_type ON Log_TypeCode = Log_StockCode
WHERE Log_StockM_WarehouseID = 1
-- warehouseid
AND Log_StockIsActive = "Y"
AND Log_StockDate BETWEEN "2022-11-01" AND "2022-11-30"
-- BETWEEN sdate AND edate

ORDER BY M_ItemName ASC, Log_StockDate asc;


END
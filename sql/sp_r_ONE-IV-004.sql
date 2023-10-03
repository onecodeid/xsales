BEGIN

SELECT DATE(Log_StockCreated) log_date,
    Log_StockRefNumber log_ref,
    M_ItemName log_item, M_ItemCode log_item_code,
    Log_TypeCode log_code,
    Log_TypeText log_desc,
    IFNULL(M_CustomerName, '') customer_name,
    IFNULL(M_VendorName, '') vendor_name,
    IFNULL(wb.M_WarehouseName, '') to_warehouse_name,
    IF(Log_StockQty > 0, Log_StockQty, 0) log_in,
    IF(Log_StockQty < 0, abs(Log_StockQty), 0) log_out
FROM one_account_aw_log.log_stock
JOIN one_account_aw_log.log_type ON Log_StockCode = Log_TypeCode
JOIN m_warehouse wa ON Log_StockM_WarehouseID = wa.M_WarehouseID
JOIN m_item ON Log_StockM_ItemID = M_ItemID
LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
LEFT JOIN m_customer ON Log_StockM_CustomerID = M_CustomerID
LEFT JOIN m_vendor ON Log_StockM_SupplierID = M_VendorID
LEFT JOIN i_transferdetail ON I_TransferDetailID = Log_StockRefID
    AND Log_StockCode LIKE "INV.TRANSFER%"
LEFT JOIN i_transfer ON I_TransferDetailI_TransferID = I_TransferID
LEFT JOIN m_warehouse wb ON I_TransferToM_WarehouseID = wb.M_WarehouseID
WHERE Log_StockM_WarehouseID = warehouseid
    AND Log_StockCreated BETWEEN CONCAT(sdate, " 00:00:00") AND CONCAT(edate, " 23:59:59")
ORDER BY Log_stockCreated, Log_StockID;

eND
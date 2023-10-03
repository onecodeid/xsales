BEGIN 

DECLARE open_balance DOUBLE;
DECLARE close_balance DOUBLE;
DECLARE total_trans_in DOUBLE;
DECLARE total_trans_out DOUBLE;

SET open_balance = (SELECT Log_StockAfterQty
    FROM one_account_aw_log.log_stock
    WHERE Log_StockM_ItemID = itemid AND Log_StockM_WarehouseID = warehouseid
    AND Log_StockCreated < concat(sdate, " 00:00:00")
    AND Log_StockIsActive = "Y"
    ORDER BY Log_StockCreated DESC, Log_StockID DESC 
    LIMIT 1);
IF open_balance IS NULL THEN SET open_balance = 0; END IF;

    SELECT SUM(IF(Log_StockQty>0, Log_StockQty, 0)), SUM(IF(Log_StockQty<0, Log_StockQty, 0))
    INTO total_trans_in, total_trans_out
    FROM one_account_aw_log.log_stock
    WHERE Log_StockM_ItemID = itemid AND Log_StockM_WarehouseID = warehouseid
    AND Log_StockCreated BETWEEN concat(sdate, " 00:00:00") AND concat(edate, " 23:59:59")
    AND Log_StockIsActive = "Y";
IF total_trans_in IS NULL THEN SET total_trans_in = 0; END IF;
IF total_trans_out IS NULL THEN SET total_trans_out = 0; END IF;
SET close_balance = open_balance + total_trans_in + total_trans_out;

SELECT open_balance, total_trans_in, total_trans_out, close_balance, M_ItemName item_name, M_WarehouseName warehouse_name
FROM m_item
JOIN m_warehouse ON M_WarehouseID = warehouseid
WHERE M_ItemID = itemid;

SELECT Log_StockID log_id, Log_StockCode log_code, date(Log_StockCreated) log_date,
    Log_StockRefID log_ref, Log_StockRefNumber log_ref_number,
    Log_StockM_CustomerID log_customer, Log_StockM_SupplierID log_supplier,
    Log_StockM_ItemID log_item,
    Log_StockM_WarehouseID log_warehouse,
    Log_StockBeforeQty log_before_qty,
    Log_StockQty log_qty,
    Log_StockAfterQty log_after_qty,
    M_ItemCode item_code, M_ItemName item_name,
    wa.M_WarehouseCode warehouse_code, wa.M_WarehouseName warehouse_name,
    IFNULL(wb.M_WarehouseCode, '') towarehouse_code, IFNULL(wb.M_WarehouseName, '') towarehouse_name,
    IFNULL(M_CustomerName, '') customer_name,
    IFNULL(M_VendorName, '') vendor_name,
    Log_TypeText type_text
FROM one_account_aw_log.log_stock
JOIN m_item ON M_ItemID = Log_StockM_ItemID
JOIN m_warehouse wa ON wa.M_WarehouseID = Log_StockM_WarehouseID
LEFT JOIN m_warehouse wb ON wb.M_WarehouseID = Log_StockFromToM_WarehouseID
JOIN one_account_aw_log.log_type ON Log_StockCode = Log_TypeCode
LEFT JOIN m_customer ON Log_StockM_CustomerID = M_CustomerID
LEFT JOIN m_vendor ON Log_StockM_SupplierID = M_VendorID
WHERE Log_StockM_ItemID = itemid AND Log_StockM_WarehouseID = warehouseid
    AND Log_StockCreated BETWEEN concat(sdate, " 00:00:00") AND concat(edate, " 23:59:59")
    AND Log_StockIsActive = "Y"
ORDER BY Log_StockCreated ASC, Log_StockID ASC;

END
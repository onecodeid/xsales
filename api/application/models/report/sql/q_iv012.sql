SELECT M_WarehouseID warehouse_id, M_WarehouseName warehouse_name,
    COUNT(DISTINCT L_DeliveryID) delivery_count, SUM(L_DeliveryDetailQty) delivery_item
FROM m_warehouse
LEFT JOIN l_delivery ON M_WarehouseID = L_DeliveryM_WarehouseID AND L_DeliveryIsActive = "Y" AND L_DeliveryDate BETWEEN ? AND ?
LEFT JOIN l_deliverydetail ON L_DeliveryDetailIsActive = "Y" 
    AND L_DeliveryDetailL_DeliveryID = L_DeliveryID

WHERE M_WarehouseIsActive = "Y"
GROUP BY M_WarehouseID
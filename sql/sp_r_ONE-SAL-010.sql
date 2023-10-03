BEGIN

SELECT L_SalesID sales_id, L_SalesDate sales_date, L_salesNumber sales_number,
    L_SalesDetailQty detail_qty, L_SalesDetailSent sent_qty, L_SalesDetailUnSent unsent_qty,
    IFNULL(SUM(L_DeliveryDetailQty), 0) delivery_qty,
    M_CustomerID customer_id, M_CustomerName customer_name, M_CustomerAddress customer_address,
    S_StaffName staff_name
FROM l_salesdetail
JOIn l_sales ON L_SalesDetailL_SalesID = L_SalesID
JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID
JOIN s_staff ON L_SalesS_StaffID = S_StaffID
JOIN m_item ON L_SalesDetailA_ItemID = M_ItemID
LEFT jOIN l_deliverydetail ON L_DeliveryDetailL_SalesDetailID = L_DeliveryDetailID
WHERE L_SalesDetailIsActive = "Y"
AND L_SalesDetailUnSent > 0
GROUP BY L_SalesDetailID
ORDER BY M_CustomerName, L_SalesNumber ASC, M_ItemName;

END
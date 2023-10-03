BEGIN

DECLARE rst TEXT;

SET rst = (SELECT CONCAT("[", GROUP_CONCAT(CONCAT('{"delivery":', `delivery`,', "items":', items,'}')), "]")
FROM (
SELECT L_InvoiceDetailL_DeliveryID delivery_id, JSON_OBJECT(
    'delivery_date', DATE_FORMAT(L_DeliveryDate, '%d-%m-%Y'),
'delivery_id', L_DeliveryID,
'delivery_note', L_DeliveryNote,
'delivery_memo', IFNULL(L_DeliveryNote, ''),
'delivery_number', L_DeliveryNumber,
'delivery_total_qty', L_DeliveryTotalQty,
'customer_id', L_DeliveryM_CustomerID,
'customer_name', M_CustomerName,
'warehouse_id', m_warehouseid,
'warehouse_name', m_warehousename,
'warehouse_shortname', m_warehouseshortname

) `delivery`, CONCAT("[", GROUP_CONCAT( JSON_OBJECT(
    'detail_id', L_InvoiceDetailID,
    'disc', L_InvoiceDetailDisc,
    'discrp', L_InvoiceDetailDiscRp,
    'include_ppn', L_InvoiceDetailIncludePPN,
    'item_id', M_ItemID,
    'item_code', M_ItemCode,
    'item_name', IFNULL(L_SalesDetailA_ItemName, M_ItemName),
    'note', "",
    'ppn', L_InvoiceDetailPPN,
    'ppn_amount', L_InvoiceDetailPPNAmount,
    'price', L_InvoiceDetailPrice,
    'purchase_id', L_InvoiceDetailL_DeliveryID,
    'qty', L_InvoiceDetailQty,
    'subtotal', L_InvoiceDetailSubTotal,
    'hpp', ((L_InvoiceDetailSubTotal * (100-L_InvoiceDiscountTotal) / 100) * (100 + L_InvoicePPNValue) / 100 / L_InvoiceDetailQty),
    'hpp_non_ppn', ((L_InvoiceDetailSubTotal * (100-L_InvoiceDiscountTotal) / 100) / L_InvoiceDetailQty),
    'ppn_value', L_InvoicePPNValue,
    'retur_qty', L_InvoiceDetailReturQty,
    'retur_nominal', L_InvoiceDetailReturNominal 


)), "]") items
FROM l_invoicedetail
JOIN l_invoice ON L_InvoiceDetailL_InvoiceID = L_InvoiceID
JOIN m_item ON M_ItemID = L_InvoiceDetailA_ItemID
JOIN l_delivery ON L_InvoiceDetailL_DeliveryID = L_DeliveryID
left join l_deliverydetail on L_DeliveryDetailA_ItemID = L_InvoiceDetailA_ItemID 
    and L_DeliveryDetailL_DeliveryID = L_DeliveryID and L_DeliveryDetailIsACtive = "Y"
left join l_salesdetail on L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
JOIN m_customer ON L_DeliveryM_CustomerID = M_CustomerID
left JOIN m_warehouse ON m_warehouseid = L_DeliveryM_WarehouseID
WHERE L_InvoiceDetailL_InvoiceID = invoice_id
AND L_InvoiceDetailIsACtive = "Y"
GROUP BY L_InvoiceDetailL_DeliveryID) x);

RETURN rst;

END
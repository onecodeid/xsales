BEGIN

DECLARE rst TEXT;

SET rst = (SELECT CONCAT("[", GROUP_CONCAT(CONCAT('{"delivery":', `sales`,', "items":', items,'}')), "]")
FROM (
SELECT L_SalesID sales_id, JSON_OBJECT(
    'sales_date', DATE_FORMAT(L_SalesDate, '%d-%m-%Y'),
    'sales_id', L_SalesID,
    'sales_note', L_SalesNote,
    'sales_memo', IFNULL(L_SalesNote, ''),
    'sales_number', L_SalesNumber,
    'sales_total_qty', 0,
    'customer_id', L_SalesM_CustomerID,
    'customer_name', M_CustomerName,
    'warehouse_id', 0,
    'warehouse_name', ''

) `sales`, CONCAT("[", GROUP_CONCAT( JSON_OBJECT(
    'disc', L_InvoiceDetailDisc,
    'discrp', L_InvoiceDetailDiscRp,
    'include_ppn', L_InvoiceDetailIncludePPN,
    'item_id', M_ItemID,
    'item_name', M_ItemName,
    'note', "",
    'ppn', L_InvoiceDetailPPN,
    'ppn_amount', L_InvoiceDetailPPNAmount,
    'price', L_InvoiceDetailPrice,
    'purchase_id', L_InvoiceDetailL_DeliveryID,
    'qty', L_InvoiceDetailQty,
    'subtotal', L_InvoiceDetailSubTotal

)), "]") items

FROM l_invoicedetail
JOIN m_item ON M_ItemID = L_InvoiceDetailA_ItemID
JOIN l_deliverydetail ON L_InvoiceDetailL_DeliveryID = L_DeliveryDetailL_DeliveryID
    AND L_DeliveryDetailA_ItemID = L_InvoiceDetailA_ItemID
    AND L_DeliveryDetailIsActive = "Y"
JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID
WHERE L_InvoiceDetailL_InvoiceID = invoice_id
AND L_InvoiceDetailIsACtive = "Y"
GROUP BY L_SalesID) x);

RETURN rst;

END
BEGIN

SELECT invoice_id, invoice_date, invoice_number,

staff_id, staff_name, customer_id, customer_name,
item_id, item_name, item_qty, item_price, 
item_disc,
(item_subtotal * item_disctotal / 100) as  item_disctotal, 
item_subtotal * (100-item_disctotal)/100 as item_subtotal, 
item_incppn, 
item_ppn,

IF(item_incppn = "N",
item_subtotal * (100-item_disctotal) * invoice_ppnvalue / (100), 
item_subtotal * (100-item_disctotal) * invoice_ppnvalue / (1 + invoice_ppnvalue) ) 
as item_ppnamount,

item_total, unit_name
FROM (
SELECT L_InvoiceID invoice_id, L_InvoiceDate invoice_date, L_InvoiceNumber invoice_number,
    -- L_InvoiceSubTotal invoice_subtotal, L_InvoiceTotal invoice_total, L_InvoiceNote invoice_note,
    L_InvoicePPNValue / 100 invoice_ppnvalue,
    S_StaffID staff_id, S_StaffName staff_name, 
    M_CustomerID customer_id, M_customerName customer_name,
M_ItemID item_id, M_ItemName item_name, L_InvoiceDetailQty item_qty,
L_InvoiceDetailPrice item_price,
-- L_InvoiceDetailDisc item_disc,	
-- L_InvoiceDetailDiscRp item_discrp,
(L_InvoiceDetailPrice * L_InvoiceDetailDisc / 100) + L_InvoiceDetailDiscRp item_disc,
L_InvoiceDiscountTotal item_disctotal,
((L_InvoiceDetailPrice * (100-L_InvoiceDetailDisc) / 100) - L_InvoiceDetailDiscRp) * L_InvoiceDetailQty item_subtotal,
-- L_InvoiceDetailSubTotal item_subtotal,
L_InvoiceDetailIncludePPN item_incppn,
L_InvoiceDetailPPN item_ppn,
L_InvoiceDetailPPNAmount item_ppnamount,
L_InvoiceDetailTotal item_total,
IFNULL(M_UnitName, '') unit_name
FROM l_invoicedetail
JOIN l_invoice ON L_InvoiceDetailL_InvoiceID = L_InvoiceID
  AND L_InvoiceDate BETWEEN sdate AND edate
JOIN m_item ON L_InvoiceDetailA_ItemID = M_ItemID
LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID

-- JOIN s_staff ON ((L_InvoiceS_StaffID = S_StaffID AND pstaff <> 0) OR pstaff = 0)
JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID

JOIN l_delivery ON L_DeliveryL_InvoiceID = L_InvoiceID AND L_DeliveryIsActive = "Y"
JOIN l_deliverydetail ON L_DeliveryDetailL_DeliveryID = L_DeliveryID AND L_DeliveryDetailIsActive = "Y"
JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
    -- AND ((L_SalesS_StaffID = pstaff AND pstaff <> 0) OR pstaff = 0)
LEFT JOIN s_staff ON (L_SalesS_StaffID = S_StaffID)
WHERE L_InvoiceDetailIsActive = "Y"
    AND ((L_InvoiceDetailM_ItemID = pitem AND pitem <> 0) OR pitem = 0)
ORDER BY L_InvoiceDate, M_ItemName) xxx ;

-- L_InvoiceDate	date NULL	
-- L_InvoiceM_TermID	int(11) [0]	
-- L_InvoiceDueDate	date NULL	
-- L_InvoiceNumber	varchar(25) NULL	
-- L_InvoiceProforma	char(1) [N]	
-- L_InvoiceP_ReceiveID	int(11) [0]	
-- L_InvoiceM_CustomerID	int(11) [0]	
-- L_InvoiceSubTotal	double [0]	
-- L_InvoiceTotal	double [0]	
-- L_InvoiceDiscount	double [0]	
-- L_InvoiceDiscountRp	double [0]	
-- L_InvoicePPN	double [0]	
-- L_InvoiceShipping	double [0]	
-- L_InvoiceGrandTotal	double [0]	
-- L_InvoiceDP	double [0]	
-- L_InvoicePaid	double [0]	
-- L_InvoiceUnpaid	double [0]	
-- L_InvoiceLunas	char(1) [N]	
-- L_InvoiceNote	varchar(255) NULL	
-- L_InvoiceT_JournalID	int(11) [0]	
-- L_InvoiceS_StaffID	int(11) [0]	
-- L_InvoiceUserID	

END
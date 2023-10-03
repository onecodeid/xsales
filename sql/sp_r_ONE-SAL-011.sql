BEGIN

SELECT L_InvoiceID invoice_id, L_InvoiceDate invoice_date, L_InvoiceNumber invoice_number,
    L_InvoiceSubTotal invoice_subtotal, L_InvoiceTotal invoice_total, L_InvoiceNote invoice_note,
    L_InvoicePPN invoice_ppn,
    S_StaffID staff_id, S_StaffName staff_name, 
    M_CustomerID customer_id, M_customerName customer_name,
    M_CustomerIsCompany customer_is_company
FROM l_invoice
-- JOIN s_staff ON ((L_InvoiceS_StaffID = S_StaffID AND pstaff <> 0) OR pstaff = 0)
JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID
    AND ((M_CustomerIsCompany = pcompany AND pcompany <> "") OR pcompany = "")
JOIN l_delivery ON L_DeliveryL_InvoiceID = L_InvoiceID AND L_DeliveryIsActive = "Y"
JOIN l_deliverydetail ON L_DeliveryDetailL_DeliveryID = L_DeliveryID AND L_DeliveryDetailIsActive = "Y"
JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
    AND ((L_SalesS_StaffID = pstaff AND pstaff <> 0) OR pstaff = 0)
LEFT JOIN s_staff ON (L_SalesS_StaffID = S_StaffID)
WHERE L_InvoiceIsActive = "Y"
AND L_InvoiceDate BETWEEN sdate AND edate
GROUP BY L_InvoiceID

ORDER BY S_StaffName, L_InvoiceDate;

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
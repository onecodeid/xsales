BEGIN

SELECT L_InvoiceID invoice_id, L_InvoiceDate invoice_date,
    L_InvoiceDueDate invoice_duedate, L_InvoiceNumber invoice_number,
    L_InvoiceTotal invoice_total, L_InvoiceGrandTotal invoice_grandtotal,
    L_InvoicePaid invoice_paid, L_InvoiceUnpaid invoice_unpaid,
    L_InvoiceLunas invoice_lunas,
    M_TermDuration term_duration, M_TermName term_name,
    M_CustomerID customer_id, M_CustomerName customer_name,
    datediff(now(), L_InvoiceDueDate) date_diff
FROM l_invoice
JOIN m_customer ON L_InvoiceM_customerID = M_CustomerID
JOIN m_term ON L_InvoiceM_TermID = M_TermID
WHERE L_InvoiceIsActive = "Y"
AND L_InvoiceUnpaid > 0
ORDER BY M_CustomerName ASC, L_InvoiceNumber ASC;

END
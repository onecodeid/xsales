select F_SpayDate pay_date, m_customerid customer_id, IF(M_CustomerCode='C.UMUM', L_SalesM_CustomerName, m_customername) customer_name,
    L_SalesID invoice_id, L_SalesDate invoice_date, "" invoice_duedate, L_SalesNumber invoice_number, L_SalesGrandTotal invoice_grandtotal, L_SalesNote invoice_note,
    L_SalesPaid invoice_paid, L_SalesUnpaid invoice_unpaid, M_TermID term_id, M_TermName term_name, M_TermDuration term_duration, '' sales_name,
    F_SpayAmount pay_amount, M_PaymentTypeName pay_type

from f_spay
JOIN l_sales ON F_SpayL_SalesID = L_SalesID
JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID
JOIN m_term ON L_SalesM_TermID = M_TermID
JOIN m_paymenttype ON F_SpayM_paymentTypeID = M_PaymentTypeID
where F_SpayIsActive = "Y" AND F_SPayDate BETWEEN ? AND ?
AND ((F_SpayM_PaymentTypeID = ? AND ? <> 0) OR ? = 0)
ORDER BY F_SpayDate asc, m_customername asc;
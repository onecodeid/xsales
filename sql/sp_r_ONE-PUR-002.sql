BEGIN

SELECT F_BillID bill_id, F_BillDate bill_date,
    F_BillDueDate bill_duedate, F_BillNumber bill_number,
    F_BillTotal bill_total, F_BillGrandTotal bill_grandtotal,
    F_BillPaid bill_paid, F_BillUnpaid bill_unpaid,
    F_BillLunas bill_lunas,
    M_TermDuration term_duration, M_TermName term_name,
    M_VendorID vendor_id, M_VendorName vendor_name,
    datediff(now(), F_BillDueDate) date_diff
FROM f_bill
JOIN m_vendor ON F_BillM_vendorID = M_VendorID
JOIN m_term ON F_BillM_TermID = M_TermID
WHERE F_BillIsActive = "Y"
AND F_BillUnpaid > 0
ORDER BY M_VendorName ASC, F_BillNumber ASC;

END
select customer_id, customer_name, sum(invoice_grandtotal) total_grandtotal, sum(pay_total) pay_total, 
    sum(invoice_paid) total_paid, sum(invoice_unpaid) total_unpaid,
    CONCAT("[", GROUP_CONCAT(JSON_OBJECT("invoice_id", invoice_id, "invoice_date", invoice_date, 
        "invoice_duedate", invoice_duedate, "invoice_number", invoice_number, "invoice_grandtotal", invoice_grandtotal,
        "invoice_paid", invoice_paid, "invoice_unpaid", invoice_unpaid, "invoice_note", invoice_note, "payments", payments,
        "term_id", term_id, "term_name", term_name, "term_duration", term_duration,
        "sales_name", sales_name)), "]") invoices

from (
    select m_customerid customer_id, m_customername customer_name,
    L_SalesID invoice_id, L_SalesDate invoice_date, "" invoice_duedate, L_SalesNumber invoice_number, L_SalesGrandTotal invoice_grandtotal, L_SalesNote invoice_note,
    L_SalesPaid invoice_paid, L_SalesUnpaid invoice_unpaid, M_TermID term_id, M_TermName term_name, M_TermDuration term_duration, '' sales_name,
    sum(f_spayamount) pay_total,
    if(f_spayid is null, "[]", concat("[", group_concat(JSON_OBJECT("pay_date", f_spaydate, "pay_number", f_spaynumber, "pay_total", f_spayamount)), "]")) payments

    from l_sales
    join m_customer on l_salesm_customerid = m_customerid
    join m_term on l_salesm_termid = m_termid
    left join f_spay on f_spayl_salesid = l_salesid and f_spayisactive = "Y"
    where l_salesunpaid > 0
    and l_salesisactive = "Y"
    and ((l_salesm_customerid = ? and ? <> 0) or ? = 0)
    and L_SalesGrandTotal > 0

    group by l_salesid
    order by m_customername) x
group by customer_id
order by customer_name

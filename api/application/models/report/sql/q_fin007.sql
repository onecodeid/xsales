select customer_id, customer_name, sum(invoice_grandtotal) total_grandtotal, sum(pay_total) pay_total, 
    sum(invoice_paid) total_paid, sum(invoice_unpaid) total_unpaid,
    CONCAT("[", GROUP_CONCAT(JSON_OBJECT("journal_date", t_journaldate, "account_name", m_accountname, "journal_debit", t_journaldetaildebit, 
        "journal_credit", t_journaldetailcredit, "invoice_id", invoice_id, "invoice_date", invoice_date, 
        "invoice_duedate", invoice_duedate, "invoice_number", invoice_number, "invoice_grandtotal", invoice_grandtotal,
        "invoice_paid", invoice_paid, "invoice_unpaid", invoice_unpaid, "payments", payments,
        "term_id", term_id, "term_name", term_name, "term_duration", term_duration,
        "sales_name", sales_name)), "]") invoices

from (
    select m_customerid customer_id, m_customername customer_name, t_journaldate, m_accountname, t_journaldetaildebit, t_journaldetailcredit,
    L_InvoiceID invoice_id, L_InvoiceDate invoice_date, L_InvoiceDueDate invoice_duedate, L_InvoiceNumber invoice_number, L_InvoiceGrandTotal invoice_grandtotal,
    L_InvoicePaid invoice_paid, L_InvoiceUnpaid invoice_unpaid, M_TermID term_id, M_TermName term_name, M_TermDuration term_duration, IFNULL(S_StaffName, '') sales_name,
    sum(f_pay2total) pay_total,
    if(f_pay2id is null, "[]", concat("[", group_concat(JSON_OBJECT("pay_date", f_pay2date, "pay_number", f_pay2number, "pay_total", f_pay2total)), "]")) payments

    from l_invoice
    join m_customer on l_invoicem_customerid = m_customerid
    join t_journal on l_invoicet_journalid = t_journalid
    join t_journaldetail on t_journaldetailt_journalid = t_journalid and (t_journaldetailisactive = "Y" or t_journaldetailisactive = "T")
    join m_account on t_journaldetailm_accountid = m_accountid
    and m_accountsystemcode = "ACC.PAYABLE"
    join m_term on l_invoicem_termid = m_termid
    left join s_staff on l_invoices_staffid = s_staffid
    left join f_pay2 on f_pay2l_invoiceid = l_invoiceid and f_pay2isactive = "Y"
    where l_invoiceunpaid > 0
    and l_invoiceisactive = "Y" and (m_customername like ? or l_invoicenumber like ?)
    and l_invoicedate between ? and ?
    and ((l_invoices_staffid = ? and ? <> 0) or ? = 0)
    group by l_invoiceid
    order by m_customername, t_journaldate) x
group by customer_id
order by customer_name

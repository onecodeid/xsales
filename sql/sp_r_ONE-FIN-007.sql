DROP PROCEDURE `sp_r_ONE-FIN-007`;
DELIMITER ;;
CREATE PROCEDURE `sp_r_ONE-FIN-007` (IN `customerid` int)

BEGIN

select m_customerid customer_id, m_customername customer_name, t_journaldate, m_accountname, t_journaldetaildebit, t_journaldetailcredit,
L_InvoiceID invoice_id, L_InvoiceDate invoice_date, L_InvoiceDueDate invoice_duedate, L_InvoiceNumber invoice_number, L_InvoiceGrandTotal invoice_grandtotal,
L_InvoicePaid invoice_paid, L_InvoiceUnpaid invoice_unpaid,
if(f_pay2id is null, "[]", concat("[", group_concat(JSON_OBJECT("pay_date", f_pay2date, "pay_number", f_pay2number, "pay_total", f_pay2total)), "]")) payments
from l_invoice
join m_customer on l_invoicem_customerid = m_customerid
join t_journal on l_invoicet_journalid = t_journalid
join t_journaldetail on t_journaldetailt_journalid = t_journalid and t_journaldetailisactive = "Y"
join m_account on t_journaldetailm_accountid = m_accountid
and m_accountsystemcode = "ACC.PAYABLE"
left join f_pay2 on f_pay2l_invoiceid = l_invoiceid and f_pay2isactive = "Y"
where l_invoiceunpaid > 0
and l_invoiceisactive = "Y"
group by l_invoiceid
order by m_customername, t_journaldate;

END;;
DELIMITER ;
BEGIN

SELECT staff_name, SUM(invoice_grandtotal-invoice_shipping+invoice_dp-invoice_ppn) ach, target, 
    SUM(invoice_grandtotal-invoice_shipping+invoice_dp-invoice_ppn) * 100 / target_year percentage, 0 hpp
FROM (
SELECT S_StaffID staff_id, S_StaffName staff_name, s_stafftargetmonth target,
  s_stafftargetyear target_year,
    L_InvoiceID invoice_id, L_InvoiceDate invoice_date, L_InvoiceNumber invoice_number,
    l_invoiceshipping invoice_shipping, l_invoicegrandtotal invoice_grandtotal, 
    L_InvoiceDP invoice_dp, L_InvoicePPN invoice_ppn,
    L_InvoiceTotal invoice_total, L_InvoiceNote invoice_note,
    
    M_CustomerID customer_id, M_customerName customer_name,
    M_CustomerIsCompany customer_is_company
    
-- select s_staffname staff_name,
-- sum(L_SalesTotal) ach, s_stafftargetmonth target, sum(L_SalesTotal) * 100 / s_stafftargetyear percentage, SUM(L_SalesTotalHPP) hpp

FROM l_invoice
JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID
JOIN l_delivery ON L_DeliveryL_InvoiceID = L_InvoiceID AND L_DeliveryIsActive = "Y"
JOIN l_deliverydetail ON L_DeliveryDetailL_DeliveryID = L_DeliveryID 
	AND L_DeliveryDetailIsActive = "Y"
JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
join s_staff on l_invoices_staffid = s_staffid

where l_invoicedate between sdate and edate
GROUP BY L_InvoiceID) x
GROUP BY staff_id;

select s_staffid staff_id, s_staffcode staff_code, s_staffname staff_name, IFNULL(offer_cnt, 0) offer_cnt, ifnull(sales_cnt, 0) sales_cnt, ifnull(convert_cnt, 0) convert_cnt, 
(ifnull(sales_cnt,0) - ifnull(convert_cnt,0)) dependen_cnt, ifnull(customer_cnt, 0) customer_cnt
from s_staff
join s_position on s_staffs_positionid = s_positionid and (s_positioncode = "POS.ADMIN" or s_positioncode = "POS.AVENGER")
LEFT JOIN (

select s_staffid staff_id, s_staffname staff_name, sum(L_LeadDetailB2B + L_LeadDetailB2C) offer_cnt
from l_lead
join l_leaddetail on l_leaddetaill_leadid = l_leadid and l_leaddetailisactive = "Y" and l_leaddetailtype = "P"
join s_staff on l_leads_staffid = s_staffid
where l_leaddate between sdate and edate
and l_leadisactive = "Y"
group by l_leads_staffid

) a on a.staff_id = s_staffid

LEFT JOIN (

select s_staffid staff_id, s_staffcode staff_code, s_staffname staff_name, count(l_salesid) sales_cnt,
count(l_salesid) convert_cnt, count(distinct l_salesm_customerid) customer_cnt
from l_sales
join s_staff on l_saless_staffid = s_staffid
where l_salesdate between sdate and edate
group by l_saless_staffid

) b on b.staff_id = s_staffid
where s_staffisactive = "Y";

END
SELECT m_itemid item_id, m_itemcode item_code, m_itemname item_name,
    sum(l_invoicedetailtotal) invoice_total, IFNULL(m_unitname, '') unit_name,
    ifnull(sum(retur_total), 0) retur_total, (sum(l_invoicedetailtotal) - ifnull(sum(retur_total), 0)) as item_total
FROM l_invoicedetail
JOIN l_invoice on l_invoicedetaill_invoiceid = l_invoiceid and l_invoicedate between ? and ? and l_invoiceisactive = "Y"

LEFT JOIN (
    SELECT l_invoicedetailid retur_invoice, sum(l_returdetailtotal) retur_total
    FROM l_returdetail 
    JOIN l_invoicedetail ON L_ReturDetailL_InvoiceDetailID = L_InvoiceDetailID AND L_InvoiceDetailISActive = "Y"
    JOIN l_invoice ON L_InvoiceDetailL_InvoiceID = L_InvoiceID AND L_InvoiceIsActive = "Y"
        AND L_InvoiceDate between ? and ?
    WHERE L_ReturDetailIsActive = "Y"
    GROUP BY l_invoicedetailid
) retur on l_invoicedetailid = retur_invoice

JOIN m_item on l_invoicedetaila_itemid = m_itemid
JOIN m_unit on m_itemm_unitid = m_unitid
where l_invoicedetailisactive = "Y"
group by m_itemid
order by item_total desc
limit 25


SELECT L_InvoiceID invoice_id, SUM(L_ReturDetailQty) retur_qty, SUM(L_ReturDetailTotal) retur_nominal,
                    L_InvoiceDetailID detail_id
                FROM l_returdetail
                JOIN l_retur ON L_returDetailL_ReturID = L_ReturID AND L_ReturIsActive = 'Y'
                JOIN l_invoicedetail ON L_ReturDetailL_InvoiceDetailID = L_InvoiceDetailID
                JOIN l_invoice ON L_InvoiceDetailL_InvoiceID = L_InvoiceID
                    -- AND L_InvoiceDate BETWEEN ? AND ?
                WHERE L_ReturDetailIsActive = 'Y'
                GROUP BY L_InvoiceDetailA_ItemID

                show columns from f_bill;
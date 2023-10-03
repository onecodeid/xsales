DROP PROCEDURE `sp_invoice_save_notrans`;
DELIMITER ;;
CREATE PROCEDURE `sp_invoice_save_notrans` (IN `invoice_id` int, IN `hdata` text, IN `delivery_ids` varchar(500), IN `uid` int)
BEGIN

DECLARE cinvoice_id INTEGER;
DECLARE invoice_date DATE;
DECLARE invoice_term INTEGER;
DECLARE invoice_due_date DATE;
DECLARE invoice_number VARCHAR(25) DEFAULT "";
DECLARE invoice_customer INTEGER;
DECLARE invoice_note VARCHAR(255);
DECLARE invoice_memo VARCHAR(255);
DECLARE invoice_disc DOUBLE;
DECLARE invoice_discrp DOUBLE;
DECLARE invoice_shipping DOUBLE;
DECLARE invoice_dp DOUBLE;
DECLARE invoice_proforma CHAR(1);
DECLARE invoice_total DOUBLE;
DECLARE delivery_id INTEGER;
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;
DECLARE total DOUBLE DEFAULT 0;
DECLARE journal_id INTEGER;
DECLARE staff_id INTEGER;

DECLARE success INTEGER DEFAULT 1;
DECLARE adata TEXT;

DECLARE account_payable_id INTEGER;
DECLARE account_revenue_id INTEGER;
DECLARE account_ppn_id INTEGER;
DECLARE account_shipping_id INTEGER;
DECLARE account_dp_id INTEGER;
DECLARE edit INTEGER DEFAULT 0;
DECLARE dps TEXT;
DECLARE ledger VARCHAR(255);

DECLARE xppn DOUBLE;
DECLARE d_ppn CHAR(1) DEFAULT "N";

-- CUSTOMER FLAG
DECLARE ocnt INTEGER DEFAULT 0;
DECLARE isnew CHAR(1);

-- DP
DECLARE dp_nett_amount DOUBLE DEFAULT 0;
DECLARE dp_ppn_amount DOUBLE DEFAULT 0;

-- DECLARE EXIT HANDLER FOR SQLEXCEPTION
-- BEGIN

-- GET DIAGNOSTICS CONDITION 1
-- @p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
-- SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

-- ROLLBACK;
-- END;

-- DECLARE EXIT HANDLER FOR SQLWARNING
-- BEGIN

-- GET DIAGNOSTICS CONDITION 1
-- @p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
-- SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

-- ROLLBACK;
-- END;

-- START TRANSACTION;

SET xppn = (SELECT fn_conf('ppn')) / 100;

SET invoice_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.date"));
SET invoice_term = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.term"));
SET invoice_due_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.due_date"));
SET invoice_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.note"));
SET invoice_memo = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.memo"));
SET invoice_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.customer"));
SET invoice_disc = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.disc"));
SET invoice_discrp = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.discrp"));
SET invoice_shipping = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.shipping"));
SET invoice_dp = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.dp"));
SET invoice_proforma = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.proforma"));
SET dps = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.dps"));

IF invoice_date IS NULL THEN SET invoice_date = date(now()); END IF;
IF invoice_due_date IS NULL THEN SET invoice_due_date = invoice_date; END IF;
IF invoice_disc IS NULL THEN SET invoice_disc = 0; END IF;
IF invoice_discrp IS NULL THEN SET invoice_discrp = 0; END IF;
IF invoice_shipping IS NULL THEN SET invoice_shipping = 0; END IF;
IF invoice_dp IS NULL THEN SET invoice_dp = 0; END IF;
IF invoice_customer IS NULL THEN SET invoice_customer = 0; END IF;
IF invoice_term IS NULL THEN SET invoice_term = 1; END IF;
IF invoice_proforma IS NULL THEN SET invoice_proforma = "N"; END IF;
IF dps IS NULL THEN SET dps = "[]"; END IF;

if invoice_date < "2022-04-01" then set xppn = 0.1; end if;

SET account_payable_id = (SELECT fn_master_get_account_id("ACC.PAYABLE"));
SET account_revenue_id = (SELECT fn_master_get_account_id("ACC.SALES"));
SET account_ppn_id = (SELECT fn_master_get_account_id("ACC.TAX.PPN"));
SET account_shipping_id = (SELECT fn_master_get_account_id("ACC.SHIPPING"));
SET account_dp_id = (SELECT fn_master_get_account_id("ACC.INCOME.PREPAID"));

IF invoice_id = 0 THEN
    SET invoice_number = (SELECT fn_numbering('INV'));
    INSERT INTO l_invoice(
        L_InvoiceDate,
        L_InvoiceM_TermID,
        L_InvoiceDueDate,
        L_InvoiceNumber,
        L_InvoiceM_CustomerID,
        L_InvoiceTotal,
        L_InvoiceDiscount,
        L_InvoiceDiscountRp,
        L_InvoiceShipping,
        L_InvoiceDp,
        L_InvoicePPN,
        L_InvoiceGrandTotal,
        L_InvoiceUnpaid,
        L_InvoiceNote,
        L_InvoiceMemo,
        L_InvoiceProforma,
        L_InvoiceUID
    ) SELECT invoice_date, invoice_term, invoice_due_date, invoice_number, invoice_customer, 0, 
        invoice_disc, invoice_discrp, invoice_shipping, invoice_dp, 0, 0,
        0, invoice_note, invoice_memo, invoice_proforma, uid;

    SET invoice_id = (SELECT LAST_INSERT_ID());
    CALL sp_log_activity("CREATE", "SALES.INVOICE", invoice_id, uid);
ELSE
    UPDATE l_invoice
    SET L_InvoiceM_TermID = invoice_term,
        L_InvoiceDate = invoice_date,
        L_InvoiceDueDate = invoice_due_date,
        L_InvoiceDiscount = invoice_disc,
        L_InvoiceDiscountRp = invoice_discrp,
        L_InvoiceShipping = invoice_shipping,
        L_InvoiceDp = invoice_dp,
        L_InvoiceNote = invoice_note,
        L_InvoiceMemo = invoice_memo,
        L_InvoiceUID = uid
    WHERE L_InvoiceID = invoice_id;
    
    
    UPDATE l_invoicedetail
    SET L_InvoiceDetailIsActive = "O"
    WHERE L_InvoiceDetailL_InvoiceID = invoice_id AND L_InvoiceDetailIsActive = "Y";

    UPDATE l_delivery
    SET L_DeliveryL_InvoiceID = 0
    WHERE L_DeliveryID IN
        (SELECT L_InvoiceDetailL_DeliveryID
        FROM l_invoicedetail
        WHERE L_InvoiceDetailL_InvoiceID = invoice_id AND L_InvoiceDetailIsActive = "O");

    SET edit = 1;
    SET invoice_number = (SELECT L_InvoiceNumber FROM l_invoice WHERE L_InvoiceID = invoice_id);
    CALL sp_log_activity("MODIFY", "SALES.INVOICE", invoice_id, uid);
END IF;


SET l = JSON_LENGTH(delivery_ids);
rcvloop : WHILE n < l Do
    SET delivery_id = JSON_UNQUOTE(JSON_EXTRACT(delivery_ids, CONCAT("$[", n, "]")));

    IF n = 0 THEN
        SET staff_id = (SELECT L_SalesS_StaffID
            FROM l_deliverydetail 
            JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
            JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
            WHERE L_DeliveryDetailL_DeliveryID = delivery_id AND L_DeliveryDetailIsActive = "Y" LIMIT 1);
        IF staff_id IS NULL THEN SET staff_id = 0; END IF;

        UPDATE l_invoice SET L_InvoiceS_StaffID = staff_id WHERE L_InvoiceID = invoice_id;
    END IF;
        
    UPDATE l_delivery
    SET L_DeliveryL_InvoiceID = invoice_id
    WHERE L_DeliveryID = delivery_id;

    IF edit = 1 THEN
        UPDATE l_invoicedetail
        JOIN l_deliverydetail ON L_InvoiceDetailL_DeliveryID = L_DeliveryDetailL_DeliveryID
            AND L_DeliveryDetailIsActive = "Y" AND L_InvoiceDetailA_ItemID = L_DeliveryDetailA_ItemID
            AND L_DeliveryDetailL_DeliveryID = delivery_id
        JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
        JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
        SET L_InvoiceDetailIsActive = "Y", 
            L_InvoiceDetailQty = L_DeliveryDetailQty,
            L_InvoiceDetailPrice = L_SalesDetailPrice,
            L_InvoiceDetailDisc = L_SalesDetailDisc,
            L_InvoiceDetailSubTotal = (
                L_DeliveryDetailQty * L_SalesDetailPrice * (100-L_SalesDetailDisc) / 100
            ),
            L_InvoiceDetailIncludePPN = L_SalesIncludePPN,
            L_InvoiceDetailPPN = L_SalesDetailPPN,
            L_InvoiceDetailPPNAmount = 0,
            L_InvoiceDetailTotal = (
                L_DeliveryDetailQty * L_SalesDetailPrice * (100-L_SalesDetailDisc) / 100
            )
        WHERE L_InvoiceDetailL_InvoiceID = invoice_id AND L_InvoiceDetailIsActive = "O";
    END IF;

    INSERT INTO l_invoicedetail(
        L_InvoiceDetailL_InvoiceID,
        L_InvoiceDetailL_DeliveryID,
        L_InvoiceDetailA_ItemID,
        L_InvoiceDetailQty,
        L_InvoiceDetailPrice,
        L_InvoiceDetailDisc,
        L_InvoiceDetailSubTotal,
        L_InvoiceDetailIncludePPN,
        L_InvoiceDetailPPN,
        L_InvoiceDetailPPNAmount,
        L_InvoiceDetailTotal
    ) SELECT invoice_id, delivery_id, L_DeliveryDetailA_ItemID, L_DeliveryDetailQty, L_SalesDetailPrice, L_SalesDetailDisc, (
        L_DeliveryDetailQty * L_SalesDetailPrice * (100-L_SalesDetailDisc) / 100
    ), L_SalesIncludePPN, L_SalesDetailPPN, 0, (
        L_DeliveryDetailQty * L_SalesDetailPrice * (100-L_SalesDetailDisc) / 100
    )
    FROM l_deliverydetail
    JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
    JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
    WHERE L_DeliveryDetailL_DeliveryID = delivery_id AND L_DeliveryDetailIsActive = "Y"
    AND L_DeliveryDetailID NOT IN (
        SELECT L_DeliveryDetailID
        FROM l_deliverydetail
        JOIN l_invoicedetail ON L_InvoiceDetailL_InvoiceID = invoice_id
            AND L_InvoiceDetailIsActive = "Y" AND L_InvoiceDetailL_DeliveryID = L_DeliveryDetailL_DeliveryID
            AND L_InvoiceDetailA_ItemID = L_DeliveryDetailA_ItemID
        WHERE L_DeliveryDetailL_DeliveryID = delivery_id AND L_DeliveryDetailIsActive = "Y"
    );

    SET n = n + 1;
END WHILE rcvloop;

UPDATE l_invoicedetail
SET L_InvoiceDetailIsActive = "N"
WHERE L_InvoiceDetailIsActive = "O" AND L_InvoiceDetailL_InvoiceID = invoice_id;

UPDATE l_invoicedetail
SET L_InvoiceDetailPPNAmount = L_InvoiceDetailSubTotal * xppn, L_InvoiceDetailTotal = L_InvoiceDetailSubTotal * (1 + xppn)
WHERE L_InvoiceDetailL_InvoiceID = invoice_id AND L_InvoiceDetailPPN = "Y" AND L_InvoiceDetailIncludePPN = "N" AND L_InvoiceDetailIsActive = "Y";

UPDATE l_invoicedetail
SET L_InvoiceDetailSubTotal = L_InvoiceDetailTotal / (1 + xppn), L_InvoiceDetailPPNAmount = L_InvoiceDetailTotal - (L_InvoiceDetailTotal / (1 + xppn))
WHERE L_InvoiceDetailL_InvoiceID = invoice_id AND L_InvoiceDetailPPN = "Y" AND L_InvoiceDetailIncludePPN = "Y" AND L_InvoiceDetailIsActive = "Y";

-- UPDATE TOTAL INVOICE
UPDATE l_invoice
JOIN (
    SELECT L_InvoiceDetailL_InvoiceID b_id, SUM(L_InvoiceDetailSubTotal) b_total, sum(L_InvoiceDetailPPNAmount) b_ppn
    FROM l_invoicedetail WHERE L_InvoiceDetailL_InvoiceID = invoice_id AND L_InvoiceDetailIsActive = "Y"
) x ON b_id = L_InvoiceID
SET L_InvoiceSubTotal = b_total, L_InvoiceTotal = (b_total * (100-L_InvoiceDiscount) / 100) - L_InvoiceDiscountRp,
    L_InvoiceDiscountTotalRp = ((b_total * L_InvoiceDiscount) / 100) + L_InvoiceDiscountRp,
    L_InvoiceDiscountTotal = IF(b_total = 0, 0, 
        (((b_total * L_InvoiceDiscount) / 100) + L_InvoiceDiscountRp) * 100 / b_total ) 
WHERE L_InvoiceID = invoice_id; 
    -- L_InvoicePPN = b_ppn, 
    -- L_InvoiceGrandTotal = (b_total * (100-L_InvoiceDiscount) / 100) - L_InvoiceDiscountRp + b_ppn + invoice_shipping - invoice_dp;

-- UPDATE GRAND TOTAL
SET d_ppn = (SELECT L_InvoiceDetailPPN FROM l_invoicedetail WHERE L_InvoiceDetailL_InvoiceID = invoice_id AND L_InvoiceDetailIsActive = "Y" LIMIT 1);
IF d_ppn <> "Y" THEN
    SET xppn = 0; END IF;

UPDATE l_invoice
SET L_InvoicePPN = L_InvoiceTotal * (xppn), 
    L_InvoiceGrandTotal = (L_InvoiceTotal * (1 + xppn)) + invoice_shipping - invoice_dp,
    L_InvoicePPNValue = xppn * 100
WHERE L_InvoiceID = invoice_id;
-- END OF GRAND TOTAL

UPDATE l_invoice
SET L_InvoiceUnpaid = L_InvoiceGrandTotal - L_InvoicePaid
WHERE L_InvoiceID = invoice_id;

SELECT L_InvoiceGrandTotal + L_InvoiceDP - L_InvoiceShipping, L_InvoiceTotal, L_InvoiceT_JournalID INTO total, invoice_total, journal_id
FROM l_invoice
WHERE L_InvoiceID = invoice_id; 

-- DP

CALL sp_invoicedp_save_notrans(invoice_id, dps, uid);
-- ENDOF DP

-- ACCOUNT SECTION

SET ledger = (SELECT CONCAT("Invoice #", invoice_number, " ", M_CustomerName) FROM m_customer WHERE M_CustomerID = invoice_customer);
SET adata = CONCAT(JSON_OBJECT("account", account_payable_id, "debit", total + invoice_shipping - invoice_dp, "credit", 0, "ledger_note", ledger), ",",
            JSON_OBJECT("account", account_revenue_id, "debit", 0, "credit", invoice_total, "ledger_note", ledger));

IF invoice_dp <> 0 THEN
    SET dp_nett_amount = (SELECT SUM(F_PaymentDpNettAmount) FROM l_invoicedp JOIN f_paymentdp ON L_InvoiceDpF_PaymentDpID = F_PaymentDpID
        WHERE L_InvoiceDpL_InvoiceID = invoice_id AND L_InvoiceDpIsActive = "Y");
    SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_dp_id, "debit", dp_nett_amount, "credit", 0, "ledger_note", ledger ));
END IF;

IF xppn <> 0 THEN
    SET dp_ppn_amount = (SELECT SUM(F_PaymentDpPPNAmount) FROM l_invoicedp JOIN f_paymentdp ON L_InvoiceDpF_PaymentDpID = F_PaymentDpID
        WHERE L_InvoiceDpL_InvoiceID = invoice_id AND L_InvoiceDpIsActive = "Y");
    IF dp_ppn_amount IS NULL THEN SET dp_ppn_amount = 0; END IF;
    SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_ppn_id, "debit", 0, "credit", ((invoice_total * xppn) - dp_ppn_amount), "ledger_note", ledger ));
END IF;

IF invoice_shipping <> 0 THEN
    SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_shipping_id, "debit", 0, "credit", invoice_shipping, "ledger_note", ledger ));
END IF;

SET adata = CONCAT("[", adata, "]");
CALL sp_journal_save_notrans(journal_id, invoice_date, invoice_number, invoice_note, adata, "J.20", account_payable_id );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = invoice_number AND T_JournalIsActive = "Y" ANd T_JournalType = "J.20");
	UPDATE l_invoice SET L_InvoiceT_JournalID = journal_id WHERE L_InvoiceID = invoice_id;
END IF;

UPDATE t_journal SET T_JournalRefID = invoice_id WHERE T_JournalID = journal_id;

-- CUSTOMER FLAG NEW SET
SET isnew = (SELECT M_CustomerIsNew FROM m_customer WHERE M_CustomerID = invoice_customer);
IF isnew = 'Y' THEN
    SET ocnt = (SELECT COUNT(L_InvoiceID) FROM l_invoice WHERE L_InvoiceM_CustomerID = invoice_customer AND L_InvoiceIsActive = "Y");
    IF ocnt > 1 THEN
        UPDATE m_customer SET M_CustomerIsNew = "N" WHERE M_CustomerID = invoice_customer;
        UPDATE l_invoice SET L_InvoiceIsNew = "N" WHERE L_InvoiceID = invoice_id;
    END IF;
END IF;


SELECT "OK" status, JSON_OBJECT("invoice_id", invoice_id, "invoice_number", invoice_number) data;
-- COMMIT;

END
;;
DELIMITER ;
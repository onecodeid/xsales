DROP PROCEDURE `sp_bill_save_single_notrans`;
DELIMITER ;;
CREATE PROCEDURE `sp_bill_save_single_notrans` (IN `bill_id` int, IN `hdata` varchar(2000), IN `receive_id` int, IN `uid` int)
BEGIN 
 
DECLARE cbill_id INTEGER; 
DECLARE bill_date DATE; 
DECLARE bill_due_date DATE; 
DECLARE bill_number VARCHAR(25); 
DECLARE bill_note VARCHAR(255); 
DECLARE bill_disc DOUBLE; 
DECLARE bill_discrp DOUBLE; 
DECLARE bill_dp DOUBLE;
DECLARE bill_ppn DOUBLE;
DECLARE term_id INTEGER;
DECLARE term_dur INTEGER DEFAULT 0;
DECLARE n INTEGER DEFAULT 0; 
DECLARE l INTEGER; 
DECLARE total DOUBLE DEFAULT 0;
DECLARE journal_id INTEGER DEFAULT 0;
DECLARE receive_id_old INTEGER;
 
DECLARE success INTEGER DEFAULT 1; 
DECLARE adata TEXT;
DECLARE ledger_note TEXT;
DECLARE vendor_id INTEGER;
DECLARE vendor_name VARCHAR(255);

DECLARE account_payable_id INTEGER;
DECLARE account_revenue_id INTEGER;
DECLARE account_ppn_id INTEGER;
DECLARE dps TEXT;
DECLARE xppn DOUBLE;
DECLARE d_ppn CHAR(1) DEFAULT "N";

SET xppn = (SELECT fn_conf('ppn')) / 100;
SET bill_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.date")); 
SET bill_due_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.due_date")); 

SET bill_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.note")); 
SET bill_disc = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.disc")); 
SET bill_discrp = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.discrp")); 
SET bill_dp = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.dp"));
SET dps = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.dps"));
SET term_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.term_id"));

IF dps IS NULL OR dps = "[]" THEN
    SET dps = (SELECT 
        CONCAT('[', GROUP_CONCAT( IF(dp_id IS NULL, NULL, JSON_OBJECT('id', dp_id, 'amount', dp_amount))), ']')
                FROM (
                SELECT P_PurchaseF_BillDpID dp_id, P_PurchaseDP dp_amount
                FROM p_receivedetail
                JOIN p_purchasedetail ON P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
                JOIN p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID AND P_PurchaseDP > 0
                WHERE P_ReceiveDetailP_ReceiveID = receive_id AND P_ReceiveDetailIsActive = "Y"
                GROUP BY P_PurchaseF_BillDpID) x );

    IF dps IS NOT NULL AND dps <> "[]" THEN
        SET bill_dp = (SELECT 
                SUM(dp_amount)
                FROM (
                SELECT P_PurchaseF_BillDpID dp_id, P_PurchaseDP dp_amount
                FROM p_receivedetail
                JOIN p_purchasedetail ON P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
                JOIN p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID AND P_PurchaseDP > 0
                WHERE P_ReceiveDetailP_ReceiveID = receive_id AND P_ReceiveDetailIsActive = "Y"
                GROUP BY P_PurchaseF_BillDpID) x );
    END IF;
END IF;

IF bill_date IS NULL THEN SET bill_date = date(now()); END IF; 
IF bill_due_date IS NULL THEN SET bill_due_date = bill_date; END IF; 
IF bill_disc IS NULL THEN SET bill_disc = 0; END IF; 
IF bill_discrp IS NULL THEN SET bill_discrp = 0; END IF; 
IF bill_dp IS NULL THEN SET bill_dp = 0; END IF;
IF dps IS NULL THEN SET dps = "[]"; END IF;

IF term_id IS NULL OR term_id = 0 THEN
    SET term_id = (SELECT P_PurchaseM_TermID
        FROM p_receivedetail
        JOIN p_purchasedetail ON P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
        JOIN p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID
        WHERE P_ReceiveDetailP_ReceiveID = receive_id
        AND P_ReceiveDetailIsActive = "Y"
        LIMIT 1);
END IF;

IF term_id <> 0 THEN
    SET term_dur = (SELECT M_TermDuration FROM m_term WHERE M_TermID = term_id);
END IF;
SET bill_due_date = DATE_ADD(bill_date, INTERVAL term_dur DAY);

SET account_payable_id = (SELECT fn_master_get_account_id("ACC.DEBT"));
SET account_revenue_id = (SELECT fn_master_get_account_id("ACC.STOCK"));
SET account_ppn_id = (SELECT fn_master_get_account_id("ACC.TAX.PPN.IN"));

IF bill_id = 0 THEN 
    SET bill_number = (SELECT fn_numbering('BILL')); 

    INSERT INTO f_bill( 
        F_BillDate, 
        F_BillDueDate, 
        F_BillNumber, 
        F_BillP_ReceiveID, 
        F_BillM_VendorID, 
        F_BillM_TermID,
        F_BillTotal, 
        F_BillDiscount, 
        F_BillDiscountRp, 
        F_BillPPN, 
        F_BillPPNValue,
        F_BillDp,
        F_BillGrandTotal, 
        F_BillUnpaid, 
        F_BillNote,
        F_BIllUID 
    ) SELECT bill_date, bill_due_date, bill_number, 0, P_ReceiveM_VendorID, term_id, 0,  
        bill_disc, bill_discrp, 0, xppn, bill_dp, 0, 
        0, bill_note, uid
    FROM p_receive WHERE P_ReceiveID = receive_id; 

    SET bill_id = (SELECT LAST_INSERT_ID());
    CALL sp_log_activity("CREATE", "PURCHASE.BILL", bill_id, uid);
ELSE
    SET bill_number = (SELECT F_BillNumber FROM f_bill WHERE F_BillID = bill_id);

    UPDATE f_bill
    SET F_BillDate = bill_date, 
        F_BillDueDate = bill_due_date,
        F_BillDiscount = bill_disc, 
        F_BillDiscountRp = bill_discrp,
        F_BillNote = bill_note,
        F_BillDp = bill_dp,
        F_BillP_ReceiveID = receive_id,
        F_BillM_TermID = term_id
    WHERE F_BillID = bill_id;

    SET receive_id_old = (SELECT F_BillP_ReceiveID FROM f_bill WHERE F_BillID = bill_id);
    CALL sp_log_activity("MODIFY", "PURCHASE.BILL", bill_id, uid);
END IF; 

SELECT M_VendorID, M_VendorName INTO vendor_id, vendor_name FROM f_bill JOIN m_vendor ON F_BillM_VendorID = M_VendorID WHERE F_BillID = bill_id;

UPDATE p_receive 
SET P_ReceiveF_BillID = bill_id 
WHERE P_ReceiveID = receive_id; 

IF receive_id_old IS NULL OR (receive_id_old IS NOT NULL AND receive_id_old <> receive_id) THEN
    IF receive_id_old IS NOT NULL AND receive_id_old <> receive_id THEN
        UPDATE f_billdetail
        SET F_BillDetailIsActive = "N"
        WHERE F_BillDetailF_BillID = bill_id AND F_BillDetailIsActive = "Y";

        UPDATE p_receive 
        SET P_ReceiveF_BillID = 0 
        WHERE P_ReceiveID = receive_id_old;
    END IF;

    INSERT INTO f_billdetail( 
        F_BillDetailF_BillID, 
        F_BillDetailP_ReceiveID, 
        F_BillDetailA_ItemID, 
        F_BillDetailQty, 
        F_BillDetailPrice, 
        F_BillDetailDisc, 
        F_BillDetailSubTotal, 
        F_BillDetailIncludePPN, 
        F_BillDetailPPN, 
        F_BillDetailPPNAmount, 
        F_BillDetailTotal 
    ) SELECT bill_id, receive_id, P_ReceiveDetailA_ItemID, P_ReceiveDetailQty, P_PurchaseDetailPrice, P_PurchaseDetailDisc, ( 
        P_ReceiveDetailQty * P_PurchaseDetailPrice * (100-P_PurchaseDetailDisc) / 100 
    ), P_PurchaseIncludePPN, P_PurchaseDetailPPN, 0, ( 
        P_ReceiveDetailQty * P_PurchaseDetailPrice * (100-P_PurchaseDetailDisc) / 100 
    ) 
    FROM p_receivedetail 
    JOIN p_purchasedetail ON P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID 
    JOIN p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID 
    WHERE P_ReceiveDetailP_ReceiveID = receive_id AND P_ReceiveDetailIsActive = "Y"; 

    
END IF;

    
UPDATE f_billdetail 
SET F_BillDetailPPNAmount = F_BillDetailSubTotal * xppn, F_BillDetailTotal = F_BillDetailSubTotal * 1 + xppn 
WHERE F_BillDetailF_BillID = bill_id AND F_BillDetailPPN = "Y" AND F_BillDetailIncludePPN = "N" AND F_BillDetailIsActive = "Y"; 
         
UPDATE f_bill
JOIN (
    SELECT F_BillDetailF_BillID b_id, SUM(F_BillDetailTotal) b_total, sum(F_BillDetailPPNAmount) b_ppn
    FROM f_billdetail WHERE F_BillDetailF_BillID = bill_id AND F_BillDetailIsActive = "Y"
) x ON b_id = F_BillID
SET F_BillTotal = b_total, F_BillPPN = b_ppn, 
    F_BillGrandTotal = (b_total * (100-F_BillDiscount) / 100) - F_BillDiscountRp + b_ppn - bill_dp;

UPDATE f_bill
SET F_BillUnpaid = F_BillGrandTotal - F_BillPaid
WHERE F_BillID = bill_id;

SELECT F_BillGrandTotal, F_BillT_JournalID INTO total, journal_id
FROM f_bill
WHERE F_BillID = bill_id; 

-- PPN
SET bill_ppn = (SELECT F_BillPPN FROM f_bill WHERE F_BillID = bill_id);

CALL sp_billdp_from_bill_save_notrans(bill_id, dps, uid);

SET ledger_note = CONCAT("Barang Masuk #", bill_number, " ", vendor_name);
SET adata = CONCAT(JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", total, "ledger_note", ledger_note), ",",
			JSON_OBJECT("account", account_revenue_id, "debit", total - bill_ppn, "credit", 0, "ledger_note", ledger_note));

-- PPN
IF bill_ppn <> 0 THEN
    SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_ppn_id, "debit", bill_ppn, "credit", 0, "ledger_note", ledger_note ));
END IF;
SET adata = CONCAT("[", adata, "]");

CALL sp_journal_save_notrans_noreturn(journal_id, bill_date, bill_number, bill_note, adata, "J.YY", account_revenue_id );

IF journal_id IS NULL OR journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = bill_number AND T_JournalIsActive = "Y" ANd T_JournalType = "J.YY");
	UPDATE f_bill SET F_BillT_JournalID = journal_id WHERE F_BillID = bill_id;
END IF;

UPDATE t_journal SET T_JournalRefID = bill_id, T_JournalReceipt = bill_number WHERE T_JournalID = journal_id;

SELECT "OK" status, JSON_OBJECT("bill_id", bill_id, "bill_number", bill_number) data;
COMMIT;

END;;
DELIMITER ;
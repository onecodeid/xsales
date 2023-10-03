BEGIN

DECLARE cbill_id INTEGER;
DECLARE bill_date DATE;
DECLARE bill_due_date DATE;
DECLARE bill_number VARCHAR(25);
DECLARE bill_note VARCHAR(255);
DECLARE bill_disc DOUBLE;
DECLARE bill_discrp DOUBLE;
DECLARE receive_id INTEGER;
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;
DECLARE total DOUBLE DEFAULT 0;
DECLARE journal_id INTEGER;

DECLARE success INTEGER DEFAULT 1;
DECLARE adata TEXT;

DECLARE account_payable_id INTEGER;
DECLARE account_revenue_id INTEGER;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

ROLLBACK;
END;

START TRANSACTION;

SET bill_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.date"));
SET bill_due_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.due_date"));
SET bill_number = (SELECT fn_numbering('BILL'));
SET bill_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.note"));
SET bill_disc = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.disc"));
SET bill_discrp = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.discrp"));

IF bill_date IS NULL THEN SET bill_date = date(now()); END IF;
IF bill_due_date IS NULL THEN SET bill_due_date = bill_date; END IF;
IF bill_disc IS NULL THEN SET bill_disc = 0; END IF;
IF bill_discrp IS NULL THEN SET bill_discrp = 0; END IF;

SET account_payable_id = (SELECT M_AccountID FROM m_account WHERE M_AccountIsActive = "Y" AND M_AccountCode = "2-20100");
SET account_revenue_id = (SELECT M_AccountID FROM m_account WHERE M_AccountIsActive = "Y" AND M_AccountCode = "5-50000");

SET l = JSON_LENGTH(receive_ids);
rcvloop : WHILE n < l Do
    SET receive_id = JSON_UNQUOTE(JSON_EXTRACT(receive_ids, CONCAT("$[", n, "]")));
    SET cbill_id = (SELECT P_ReceiveF_BillID FROM p_receive WHERE P_ReceiveID = receive_id);

    IF cbill_id <> 0 THEN
        
        SET success = 0;
        LEAVE rcvloop;
    ELSE
        
        IF bill_id = 0 THEN
            INSERT INTO f_bill(
                F_BillDate,
                F_BillDueDate,
                F_BillNumber,
                F_BillP_ReceiveID,
                F_BillM_VendorID,
                F_BillTotal,
                F_BillDiscount,
                F_BillDiscountRp,
                F_BillPPN,
                F_BillGrandTotal,
                F_BillUnpaid,
                F_BillNote
            ) SELECT bill_date, bill_due_date, bill_number, 0, P_ReceiveM_VendorID, 0, 
                bill_disc, bill_discrp, 0, 0,
                0, bill_note
            FROM p_receive WHERE P_ReceiveID = receive_id;

            SET bill_id = (SELECT LAST_INSERT_ID());
        END IF;

        
        UPDATE p_receive
        SET P_ReceiveF_BillID = bill_id
        WHERE P_ReceiveID = receive_id;

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

        
        UPDATE f_billdetail
        SET F_BillDetailPPNAmount = F_BillDetailSubTotal * 0.1, F_BillDetailTotal = F_BillDetailSubTotal * 1.1
        WHERE F_BillDetailF_BillID = bill_id AND F_BillDetailPPN = "Y" AND F_BillDetailIncludePPN = "N" AND F_BillDetailIsActive = "Y";

        
        UPDATE f_billdetail
        SET F_BillDetailSubTotal = F_BillDetailTotal / 1.1, F_BillDetailPPNAmount = F_BillDetailTotal - (F_BillDetailTotal / 1.1)
        WHERE F_BillDetailF_BillID = bill_id AND F_BillDetailPPN = "Y" AND F_BillDetailIncludePPN = "Y" AND F_BillDetailIsActive = "Y";

        
        UPDATE f_bill
        JOIN (
            SELECT F_BillDetailF_BillID b_id, SUM(F_BillDetailSubTotal) b_total, sum(F_BillDetailPPNAmount) b_ppn
            FROM f_billdetail WHERE F_BillDetailF_BillID = bill_id AND F_BillDetailIsActive = "Y"
        ) x ON b_id = F_BillID
        SET F_BillTotal = b_total, F_BillPPN = b_ppn, 
            F_BillGrandTotal = (b_total * (100-F_BillDiscount) / 100) - F_BillDiscountRp + b_ppn;

        UPDATE f_bill
        SET F_BillUnpaid = F_BillGrandTotal - F_BillPaid
        WHERE F_BillID = bill_id;

    END IF;

    SET n = n + 1;
END WHILE rcvloop;

SELECT F_BillGrandTotal, F_BillT_JournalID INTO total, journal_id
FROM f_bill
WHERE F_BillID = bill_id; 

-- LAST PURCHASE LOG
-- call sp_bill_into_last_purchase(bill_id);

SET adata = JSON_ARRAY(JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", total),
			JSON_OBJECT("account", account_revenue_id, "debit", total, "credit", 0));
CALL sp_journal_save_notrans(journal_id, bill_date, bill_number, bill_note, adata, "J.YY", account_revenue_id );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = bill_number AND T_JournalIsActive = "Y" ANd T_JournalType = "J.YY");
	UPDATE f_bill SET F_BillT_JournalID = journal_id WHERE F_BillID = bill_id;
END IF;

IF success = 1 THEN
    SELECT "OK" status, JSON_OBJECT("bill_id", bill_id, "bill_number", bill_number) data;
    COMMIT;
ELSE
    SELECT "ERR" status, "Invoice sudah dibuat untuk faktur tersebut :(" message;
    ROLLBACK;
END IF;


END
BEGIN

DECLARE jtype CHAR(4);
DECLARE jpost CHAR(1);
DECLARE paymentid INTEGER;

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

SET jtype = (SELECT T_JournalType FROM t_journal WHERE T_JournalID = journalid);
SET jpost = (SELECT T_JournalPost FROM t_journal WHERE T_JournalID = journalid);

IF jpost = "N" THEN
    UPDATE t_journal SET T_JournalIsActive = "N" WHERE T_JournalID = journalid;
    UPDATE t_journaldetail SET T_JournalDetailIsActive = "N" WHERE T_JournalDetailT_JournalID = journalid;

    IF jtype = "J.11" THEN
    -- PENERIMAAN PIUTANG
        SET paymentid = (SELECT F_Payment2ID FROM f_payment2 WHERE F_Payment2T_JournalID = journalid AND F_Payment2IsActive = "Y" LIMIT 1);
        

        -- UPDATE INVOICE
        UPDATE l_invoice
        JOIN (
            SELECT F_Payment2DetailL_InvoiceID invoice_id, SUM(F_Payment2DetailAmount) amnt
            FROM f_payment2detail
            WHERE F_Payment2DetailIsActive = "Y" AND F_Payment2DetailF_Payment2ID = paymentid
            GROUP BY F_Payment2DetailL_InvoiceID
        ) x on invoice_id = L_InvoiceID
        SET L_InvoicePaid = L_InvoicePaid - amnt;

        -- UPDATE RETUR
        -- UPDATE one_iv.l_salesretur
        -- JOIN (
        --     SELECT F_Payment2DetailL_SalesReturID retur_id, SUM(F_Payment2DetailAmount) amnt
        --     FROM f_payment2detail
        --     WHERE F_Payment2DetailIsActive = "Y" AND F_Payment2DetailF_Payment2ID = paymentid AND F_Payment2DetailIsRetur = "Y" AND F_Payment2DetailL_SalesReturID <> 0
        --     GROUP BY F_Payment2DetailL_SalesReturID
        -- ) x on retur_id = L_SalesReturID
        -- SET L_SalesReturUsed = L_SalesReturUsed - amnt;

        UPDATE f_payment2 SET F_Payment2IsActive = "N" WHERE F_Payment2ID = paymentid;
        UPDATE f_payment2detail SET F_Payment2DetailIsActive = "N" WHERE F_Payment2DetailF_Payment2ID = paymentid;
        
    ELSEIF jtype = "J.12" THEN
    -- PEMBAYARAN HUTANG
        SET paymentid = (SELECT F_BillPaymentID FROM f_billpayment WHERE F_BillPaymentT_JournalID = journalid AND F_BillPaymentIsActive = "Y" LIMIT 1);

        -- UPDATE PURCHASE / BILL        
        -- UPDATE p_purchase
        -- JOIN (
        --     SELECT F_BillPaymentDetailP_PurchaseID bill_id, SUM(F_BillPaymentDetailAmount) amnt
        --     FROM f_billpaymentdetail
        --     WHERE F_BillPaymentDetailIsActive = "Y" AND F_BillPaymentDetailF_BillPaymentID = paymentid
        --     GROUP BY F_BillPaymentDetailP_PurchaseID
        -- ) x on bill_id = P_PurchaseID
        -- SET P_PurchasePaid = P_PurchasePaid - amnt;

        -- UPDATE RETUR
        -- UPDATE p_purchaseretur
        -- JOIN (
        --     SELECT F_BillPaymentDetailP_PurchaseReturID retur_id, SUM(F_BillPaymentDetailAmount) amnt
        --     FROM f_billpaymentdetail
        --     WHERE F_BillPaymentDetailIsActive = "Y" AND F_BillPaymentDetailF_BillPaymentID = paymentid AND F_BillPaymentDetailIsRetur = "Y" AND F_BillPaymentDetailP_PurchaseReturID <> 0
        --     GROUP BY F_BillPaymentDetailP_PurchaseReturID
        -- ) x on retur_id = P_PurchaseReturID
        -- SET P_PurchaseReturUsed = P_PurchaseReturUsed - amnt;

        UPDATE f_billpayment SET F_BillPaymentIsActive = "N" WHERE F_BillPaymentID = paymentid;
        UPDATE f_billpaymentdetail SET F_BillPaymentDetailIsActive = "N" WHERE F_BillPaymentDetailF_BillPaymentID = paymentid;
    ELSEIF jtype = "J.13" OR jtype = "J.14" THEN
        SET paymentid = (SELECT F_ReceiveID FROM f_receive WHERE F_ReceiveT_JournalID = journalid AND F_ReceiveIsActive = "Y" LIMIT 1);
        UPDATE f_receive SET F_ReceiveIsActive = "N" WHERE F_ReceiveID = paymentid;
        UPDATE f_receivedetail SET F_ReceiveDetailIsActive = "N" WHERE F_ReceiveDetailF_ReceiveID = paymentid;
    END IF;

    SELECT "OK" status, JSON_OBJECT("journal_id", journalid, "payment_id", paymentid) data;
ELSE
    SELECT "ERR" status, "Jurnal tersebut telah diposting !" message;
END IF;

COMMIT;

END
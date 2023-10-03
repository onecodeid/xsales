DROP PROCEDURE `sp_finance_pay2_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_pay2_save` (IN `pay_id` int, IN `hdata` text, IN `uid` int)
BEGIN

DECLARE pay_date DATE;
DECLARE pay_number VARCHAR(25);
DECLARE invoice_id INTEGER;
DECLARE customer_id INTEGER;
DECLARE pay_amount DOUBLE;
DECLARE pay_disc DOUBLE;
DECLARE pay_discrp DOUBLE;
DECLARE pay_discamount DOUBLE;
DECLARE pay_credit_account INTEGER;
DECLARE pay_disc_account INTEGER;
DECLARE pay_note TEXT;
DECLARE pay_memo TEXT;
DECLARE pay_receipt TEXT;
DECLARE pay_total DOUBLE;

DECLARE memos TEXT;
DECLARE memo_amount DOUBLE DEFAULT 0;

DECLARE invoice_av DOUBLE;
DECLARE old_pay_amount DOUBLE DEFAULT 0;

-- MEMOS
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;
DECLARE tmp TEXT;
DECLARE memo_id INTEGER;
DECLARE memo_amnt DOUBLE;
DECLARE old_memo_amnt DOUBLE;
DECLARE pay2_memo_id INTEGER;
DECLARE memo_unused DOUBLE;
DECLARE memo_acc INTEGER;
DECLARE memo_iv INTEGER;

-- ACCOUNT
DECLARE account_payable_id INTEGER;
DECLARE account_debt_id INTEGER;
DECLARE adata TEXT;
DECLARE journal_id INTEGER DEFAULT 0;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME, @clm = COLUMN_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name, @clm as column_name;
ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME, @clm = COLUMN_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name, @clm as column_name;

ROLLBACK;
END;

START TRANSACTION;

SET pay_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_date'));
SET pay_amount = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_amount'));
SET pay_disc = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_disc'));
SET pay_discrp = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_discrp'));
SET pay_discamount = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_discamount'));
SET invoice_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.invoice_id'));
SET pay_credit_account = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_credit_account'));
SET pay_disc_account = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_disc_account'));
SET pay_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_note'));
SET pay_memo = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_memo'));
SET pay_receipt = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_receipt'));

SET memo_amount = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.memo_amount'));
SET memos = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.memos'));

SET account_payable_id = (SELECT fn_master_get_account_id("ACC.PAYABLE"));
SET account_debt_id = (SELECT fn_master_get_account_id("ACC.DEBT"));

IF memo_amount IS NULL THEN SET memo_amount = 0; END IF;
IF memos IS NULL THEN SET memos = "[]"; END IF;
IF pay_disc_account IS NULL THEN SET pay_disc_account = 0; END If;

IF pay_id = 0 THEN
    SET pay_number = (SELECT fn_numbering("FPAY"));
    SET invoice_av = (SELECT (L_InvoiceUnpaid) FROM l_invoice WHERE L_InvoiceID = invoice_id);
    
    INSERT INTO f_pay2(
        F_Pay2Date,
        F_Pay2Number,
        F_Pay2L_InvoiceID,
        F_Pay2M_CustomerID,
        F_Pay2Amount,
        F_Pay2CreditM_AccountID,
        F_Pay2Disc,
        F_Pay2DiscRp,
        F_Pay2DiscAmount,
        F_Pay2DiscM_AccountID,
        F_Pay2Memos,
        F_Pay2MemoAmount,
        F_Pay2Note,
        F_Pay2Memo,
        F_Pay2Receipt,
        F_Pay2UID)
    SELECT pay_date, pay_number, invoice_id, L_InvoiceM_CustomerID, pay_amount, pay_credit_account, pay_disc, pay_discrp, pay_discamount, pay_disc_account, 
    memos, memo_amount, pay_note, pay_memo, pay_receipt, uid
    FROM l_invoice WHERE L_InvoiceID = invoice_id;

    SET pay_id = (SELECT LAST_INSERT_ID());
    CALL sp_log_activity("CREATE", "FINANCE.PAY", pay_id, uid);
ELSE
    SELECT F_Pay2Number, F_Pay2L_InvoiceID INTO pay_number, invoice_id 
    FROM f_pay2 WHERE F_Pay2ID = pay_id;
    
    SET invoice_av = (SELECT (L_InvoiceUnpaid) FROM l_invoice WHERE L_InvoiceID = invoice_id);
    SET old_pay_amount = (SELECT F_Pay2Amount + F_Pay2DiscAmount + F_Pay2MemoAmount FROM f_pay2 WHERE F_Pay2ID = pay_id);
    SET invoice_av = invoice_av + old_pay_amount;

    UPDATE f_pay2
    SET F_Pay2Date = pay_date,
        F_Pay2Amount = pay_amount,
        F_Pay2CreditM_AccountID = pay_credit_account,
        F_Pay2Disc = pay_disc,
        F_Pay2DiscRp = pay_discrp,
        F_Pay2DiscAmount = pay_discamount,
        F_Pay2DiscM_AccountID = pay_disc_account,
        F_Pay2Memos = memos,
        F_Pay2MemoAmount = memo_amount,
        F_Pay2Note = pay_note,
        F_Pay2Memo = pay_memo,
        F_Pay2Receipt = pay_receipt
    WHERE F_Pay2ID = pay_id;

    SET journal_id = (SELECT F_Pay2T_JournalID FROM f_pay2 WHERE F_Pay2ID = pay_id);
    CALL sp_log_activity("MODIFY", "FINANCE.PAY", pay_id, uid);
END IF;

IF (pay_amount - invoice_av) > 0.5 THEN
    SELECT "ERR" status, "Jumlah Pembayaran Melebihi Tagihan !" message;
    ROLLBACK;
ELSE
    UPDATE l_invoice SET L_InvoicePaid = L_InvoicePaid + pay_amount + pay_discamount + memo_amount - old_pay_amount
    WHERE L_InvoiceID = invoice_id;

    UPDATE l_invoice SET L_InvoiceUnpaid = L_InvoiceGrandTotal - L_InvoicePaid WHERE L_InvoiceID = invoice_id;

    -- MEMOS
    UPDATE f_pay2memo
    SET F_Pay2MemoIsActive = "O"
    WHERE F_Pay2MemoIsActive = "Y" AND F_Pay2MemoF_Pay2ID = pay_id;

    SET l = JSON_LENGTH(memos);
    WHILE n < l DO
        SET tmp = JSON_EXTRACT(memos, CONCAT("$[", n, "]"));
        SET n = n+1;

        SET memo_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.memo_id"));
        SET memo_amnt = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.amount"));

        SELECT F_MemoL_InvoiceID, F_MemoM_AccountID INTO memo_iv, memo_acc
        FROM f_memo WHERE F_MEmoID = memo_id;

        SET pay2_memo_id = (SELECT F_Pay2MemoID FROM f_pay2memo WHERE F_Pay2MemoF_Pay2ID = pay_id AND F_Pay2MemoF_MemoID = memo_id AND F_Pay2MemoIsActive = "O");
        IF pay2_memo_id IS NULL THEN
            INSERT INTO f_pay2memo(
                F_Pay2MemoF_Pay2ID,
                F_Pay2MemoL_InvoiceID,
                F_Pay2MemoF_MemoID,
                F_Pay2MemoM_CustomerID,
                F_Pay2MemoAmount
            )
            SELECT pay_id, F_Pay2L_InvoiceID, memo_id, F_Pay2M_CustomerID, memo_amnt FROM f_pay2 WHERE F_Pay2ID = pay_id;
            UPDATE f_memo SET F_MemoUsed = F_MemoUsed + memo_amnt WHERE F_MemoID = memo_id;
        ELSE
            SET old_memo_amnt = (SELECT F_Pay2MemoAmount FROM f_pay2memo WHERE F_Pay2MemoID = pay2_memo_id);

            UPDATE f_pay2memo SET F_Pay2MemoAmount = memo_amnt, F_Pay2MemoIsActive = "Y" WHERE F_Pay2MemoID = pay2_memo_id;
            UPDATE f_memo SET F_MemoUsed = F_MemoUsed + memo_amnt - old_memo_amnt WHERE F_MemoID = memo_id;
        END IF;

        SET memo_unused = (SELECT F_MemoAmount - F_MemoUsed - F_MemoRefunded FROM f_memo WHERE F_MemoID = memo_id);
        IF memo_unused < 0 THEN
            SET n = 999;
            SELECT "ERR" as status, "Jumlah KREDIT MEMO tidak mencukupi, silahkan dicek kembali !" as message;
            ROLLBACK;
        END IF;
    END WHILE;

    UPDATE f_memo
    JOIN f_pay2memo ON F_Pay2MemoF_Pay2ID = pay_id AND F_Pay2MemoIsActive = "O" AND F_Pay2MemoF_MemoID = F_MemoID
    SET F_MemoUsed = F_MemoUsed - F_Pay2MemoAmount;

    UPDATE f_pay2memo SET F_Pay2MemoIsActive = "N" WHERE F_Pay2MemoF_Pay2ID = pay_id AND F_Pay2MemoIsActive = "O";

    SET pay_total = (SELECT F_Pay2Amount + F_Pay2DiscAmount + F_Pay2MemoAmount FROM f_pay2 WHERE F_Pay2ID = pay_id);
    UPDATE f_pay2 SET F_Pay2Total = pay_total WHERE F_Pay2ID = pay_id;

    -- ACCOUNT
    -- ACCOUNT SECTION
    SET @ldg = (SELECT CONCAT("Penerimaan #", pay_number, " ", M_CustomerName) FROM l_invoice JOIN m_customer ON L_InvoiceM_CustomerID = M_CustomerID WHERE L_InvoiceID = invoice_id);
    SET adata = CONCAT(JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", pay_total, "ledger_note", @ldg), ",",
            JSON_OBJECT("account", pay_credit_account, "debit", pay_amount, "credit", 0, "ledger_note", @ldg));

    IF pay_discamount > 0 THEN
        SET adata = CONCAT(adata, ",", JSON_OBJECT("account", pay_disc_account, "debit", pay_discamount, "credit", 0, "ledger_note", @ldg));
    END IF;

    IF memo_amount > 0 THEN
        IF memo_acc <> 0 AND memo_iv = 0 THEN SET account_debt_id = (SELECT fn_master_get_account_id("ACC.INCOME.PREPAID")); END IF;
        SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_debt_id, "debit", memo_amount, "credit", 0, "ledger_note", @ldg));
    END IF;

    SET adata = CONCAT("[", adata, "]");
    CALL sp_journal_save_notrans_noreturn(journal_id, pay_date, pay_number, pay_note, adata, "J.11", pay_credit_account );

    IF journal_id = 0 THEN
        SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = pay_number AND (T_JournalIsActive = "Y" OR T_JournalIsActive = "T") ANd T_JournalType = "J.11");
        UPDATE f_pay2 SET F_Pay2T_JournalID = journal_id WHERE F_Pay2ID = pay_id;
    END IF;

    UPDATE t_journal SET T_JournalRefID = pay_id WHERE T_JournalID = journal_id;

    SELECT "OK" as status, JSON_OBJECT("pay_id", pay_id, "pay_number", pay_number) data;
    COMMIT;
END IF;

END;;
DELIMITER ;
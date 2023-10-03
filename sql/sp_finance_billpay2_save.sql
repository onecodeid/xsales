DROP PROCEDURE `sp_finance_billpay2_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_billpay2_save` (IN `pay_id` int, IN `hdata` text, IN `uid` int)
BEGIN

DECLARE pay_date DATE;
DECLARE pay_number VARCHAR(25);
DECLARE bill_id INTEGER;
DECLARE vendor_id INTEGER;
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

DECLARE bill_av DOUBLE;
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

-- ACCOUNT
DECLARE account_payable_id INTEGER;
DECLARE account_debt_id INTEGER;
DECLARE account_dp_id INTEGER;
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
SET bill_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bill_id'));
SET pay_credit_account = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_credit_account'));
SET pay_disc_account = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_disc_account'));
SET pay_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_note'));
SET pay_memo = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_memo'));
SET pay_receipt = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_receipt'));

SET memo_amount = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.memo_amount'));
SET memos = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.memos'));

IF memo_amount IS NULL THEN SET memo_amount = 0; END IF;
IF memos IS NULL THEN SET memos = "[]"; END IF;
IF pay_disc_account IS NULL THEN SET pay_disc_account = 0; END If;

IF pay_id = 0 THEN
    SET pay_number = (SELECT fn_numbering("FPAY"));
    SET bill_av = (SELECT (F_BillUnpaid) FROM f_bill WHERE F_BillID = bill_id);
    
    INSERT INTO f_billpay2(
        F_BillPay2Date,
        F_BillPay2Number,
        F_BillPay2F_BillID,
        F_BillPay2M_VendorID,
        F_BillPay2Amount,
        F_BillPay2CreditM_AccountID,
        F_BillPay2Disc,
        F_BillPay2DiscRp,
        F_BillPay2DiscAmount,
        F_BillPay2DiscM_AccountID,
        F_BillPay2Memos,
        F_BillPay2MemoAmount,
        F_BillPay2Note,
        F_BillPay2Memo,
        F_BillPay2Receipt)
    SELECT pay_date, pay_number, bill_id, F_BillM_VendorID, pay_amount, pay_credit_account, pay_disc, pay_discrp, pay_discamount, pay_disc_account, 
    memos, memo_amount, 
    pay_note, 
    pay_memo, 
    pay_receipt
    FROM f_bill WHERE F_BillID = bill_id;

    SET pay_id = (SELECT LAST_INSERT_ID());
ELSE
    SELECT F_BillPay2Number, F_BillPay2F_BillID INTO pay_number, bill_id 
    FROM f_billpay2 WHERE F_BillPay2ID = pay_id;
    
    SET bill_av = (SELECT (F_BillUnpaid) FROM f_bill WHERE F_BillID = bill_id);
    SET old_pay_amount = (SELECT F_BillPay2Amount + F_BillPay2DiscAmount + F_BillPay2MemoAmount FROM f_billpay2 WHERE F_BillPay2ID = pay_id);
    -- SET old_pay_amount = (SELECT F_BillPay2Amount + F_BillPay2DiscAmount + F_BillPay2MemoAmount FROM f_billpay2 WHERE F_BillPay2ID = pay_id);
    SET bill_av = bill_av + old_pay_amount;

    UPDATE f_billpay2
    SET F_BillPay2Date = pay_date,
        F_BillPay2Amount = pay_amount,
        F_BillPay2CreditM_AccountID = pay_credit_account,
        F_BillPay2Disc = pay_disc,
        F_BillPay2DiscRp = pay_discrp,
        F_BillPay2DiscAmount = pay_discamount,
        F_BillPay2DiscM_AccountID = pay_disc_account,
        F_BillPay2Memos = memos,
        F_BillPay2MemoAmount = memo_amount,
        F_BillPay2Note = pay_note,
        F_BillPay2Memo = pay_memo,
        F_BillPay2Receipt = pay_receipt
    WHERE F_BillPay2ID = pay_id;

    SET journal_id = (SELECT F_BillPay2T_JournalID FROM f_billpay2 WHERE F_BillPay2ID = pay_id);
END IF;

IF pay_amount - bill_av > 1 THEN
    SELECT "ERR" status, "Jumlah Pembayaran Melebihi Tagihan !" message;
    ROLLBACK;
ELSE
    -- UPDATE f_bill SET F_BillPaid = F_BillPaid + pay_amount + pay_discamount + memo_amount - old_pay_amount
    UPDATE f_bill SET F_BillPaid = F_BillPaid + pay_amount + pay_discamount + memo_amount - old_pay_amount
    WHERE F_BillID = bill_id;

    UPDATE f_bill SET F_BillUnpaid = F_BillGrandTotal - F_BillPaid WHERE F_BillID = bill_id;

    SET pay_total = (SELECT F_BillPay2Amount + F_BillPay2DiscAmount + F_BillPay2MemoAmount FROM f_billpay2 WHERE F_BillPay2ID = pay_id);
    UPDATE f_billpay2 SET F_BillPay2Total = pay_total WHERE F_BillPay2ID = pay_id;

    -- MEMOS
    UPDATE f_billpay2memo
    SET F_BillPay2MemoIsActive = "O"
    WHERE F_BillPay2MemoIsActive = "Y" AND F_BillPay2MemoF_BillPay2ID = pay_id;

    SET l = JSON_LENGTH(memos);
    WHILE n < l DO
        SET tmp = JSON_EXTRACT(memos, CONCAT("$[", n, "]"));
        SET n = n+1;

        SET memo_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.memo_id"));
        SET memo_amnt = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.amount"));

        SET pay2_memo_id = (SELECT F_BillPay2MemoID FROM f_billpay2memo WHERE F_BillPay2MemoF_BillPay2ID = pay_id AND F_BillPay2MemoF_MemoDebitID = memo_id AND F_BillPay2MemoIsActive = "O");
        IF pay2_memo_id IS NULL THEN
            INSERT INTO f_billpay2memo(
                F_BillPay2MemoF_BillPay2ID,
                F_BillPay2MemoF_BillID,
                F_BillPay2MemoF_MemoDebitID,
                F_BillPay2MemoM_VendorID,
                F_BillPay2MemoAmount
            )
            SELECT pay_id, F_BillPay2F_BillID, memo_id, F_BillPay2M_VendorID, memo_amnt FROM f_billpay2 WHERE F_BillPay2ID = pay_id;
            UPDATE f_memodebit SET F_MemoDebitUsed = F_MemoDebitUsed + memo_amnt WHERE F_MemoDebitID = memo_id;
        ELSE
            SET old_memo_amnt = (SELECT F_BillPay2MemoAmount FROM f_billpay2memo WHERE F_BillPay2MemoID = pay2_memo_id);

            UPDATE f_billpay2memo SET F_BillPay2MemoAmount = memo_amnt, F_BillPay2MemoIsActive = "Y" WHERE F_BillPay2MemoID = pay2_memo_id;
            UPDATE f_memodebit SET F_MemoDebitUsed = F_MemoDebitUsed + memo_amnt - old_memo_amnt WHERE F_MemoDebitID = memo_id;
        END IF;

        SET memo_unused = (SELECT F_MemoDebitAmount - F_MemoDebitUsed - F_MemoDebitRefunded FROM f_memodebit WHERE F_MemoDebitID = memo_id);
        IF memo_unused < 0 THEN
            SET n = 999;
            SELECT "ERR" as status, "Jumlah DEBIT MEMO tidak mencukupi, silahkan dicek kembali !" as message;
            ROLLBACK;
        END IF;
    END WHILE;

    UPDATE f_memodebit
    JOIN f_billpay2memo ON F_BillPay2MemoF_BillPay2ID = pay_id AND F_BillPay2MemoIsActive = "O" AND F_BillPay2MemoF_MemoDebitID = F_MemoDebitID
    SET F_MemoDebitUsed = F_MemoDebitUsed - F_BillPay2MemoAmount;

    UPDATE f_billpay2memo SET F_BillPay2MemoIsActive = "N" WHERE F_BillPay2MemoF_BillPay2ID = pay_id AND F_BillPay2MemoIsActive = "O";

    -- ACCOUNT
    -- ACCOUNT SECTION
    -- SET account_payable_id = (SELECT fn_master_get_account_id("ACC.PAYABLE"));
    SET account_debt_id = (SELECT fn_master_get_account_id("ACC.DEBT"));
    SET account_dp_id = (SELECT fn_master_get_account_id("ACC.DP"));

    SET @ldg = (SELECT CONCAT("Pembayaran #", pay_number, " ", M_VendorName) FROM f_bill JOIN m_vendor ON F_BillM_VendorID = M_VendorID WHERE F_BillID = bill_id);
    SET adata = CONCAT(JSON_OBJECT("account", account_debt_id, "debit", pay_total, "credit", 0, "ledger_note", @ldg), ",",
            JSON_OBJECT("account", pay_credit_account, "debit", 0, "credit", pay_amount, "ledger_note", @ldg));

    IF pay_discamount > 0 THEN
        SET adata = CONCAT(adata, ",", JSON_OBJECT("account", pay_disc_account, "debit", 0, "credit", pay_discamount, "ledger_note", @ldg));
    END IF;

    IF memo_amount > 0 THEN
        SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_dp_id, "debit", 0, "credit", memo_amount, "ledger_note", @ldg));
    END IF;

    SET adata = CONCAT("[", adata, "]");
    CALL sp_journal_save_notrans_noreturn(journal_id, pay_date, pay_number, pay_note, adata, "J.12", pay_credit_account );

    IF journal_id = 0 THEN
        SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = pay_number AND (T_JournalIsActive = "Y" OR T_JournalIsActive = "T") ANd T_JournalType = "J.12");
        UPDATE f_billpay2 SET F_BillPay2T_JournalID = journal_id WHERE F_BillPay2ID = pay_id;
    END IF;

    UPDATE t_journal SET T_JournalRefID = pay_id WHERE T_JournalID = journal_id;

    SELECT "OK" as status, JSON_OBJECT("pay_id", pay_id, "pay_number", pay_number) data;
    COMMIT;
END IF;

END
;;
DELIMITER ;
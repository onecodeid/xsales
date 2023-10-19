DROP PROCEDURE `sp_finance_spay_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_spay_save` (IN `pay_id` int, IN `hdata` text, IN `uid` int)
BEGIN

DECLARE pay_date DATE;
DECLARE pay_number VARCHAR(25);
DECLARE sales_id INTEGER;
DECLARE pay_amount DOUBLE;
DECLARE pay_note TEXT;
DECLARE pay_type INTEGER;

DECLARE paid_total DOUBLE;

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
SET sales_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_sales'));
SET pay_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_note'));
SET pay_type = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_type'));

IF pay_id = 0 THEN
    SET pay_number = (SELECT fn_numbering("FPAY"));
    
    INSERT INTO f_spay(
        F_SpayDate,
        F_SpayNumber,
        F_SpayL_SalesID,
        -- F_SpayM_CustomerID,
        F_SpayAmount,
        F_SpayM_PaymentTypeID,
        F_SpayNote)
    SELECT pay_date, pay_number, sales_id, pay_amount, pay_type, pay_note;

    SET pay_id = (SELECT LAST_INSERT_ID());
ELSE

    SELECT F_SpayNumber, F_SpayL_SalesID INTO pay_number, sales_id 
    FROM f_spay WHERE F_SpayID = pay_id;
    
    -- SET invoice_av = (SELECT (L_SalesUnpaid) FROM l_sales WHERE L_SalesID = sales_id);
    SET old_pay_amount = (SELECT F_SpayAmount FROM f_spay WHERE F_SpayID = pay_id);
    -- SET invoice_av = invoice_av + old_pay_amount;

    UPDATE f_spay
    SET F_SpayDate = pay_date,
        F_SpayAmount = pay_amount,
        F_SpayM_PaymentTypeID = pay_type,
        F_SpayNote = pay_note
    WHERE F_SpayID = pay_id;

    -- SET journal_id = (SELECT F_SpayT_JournalID FROM f_spay WHERE F_SpayID = pay_id);
    -- CALL sp_log_activity("MODIFY", "FINANCE.PAY", pay_id, uid);
END IF;

-- TOTALIZE
SET paid_total = (SELECT SUM(F_SpayAmount) FROM f_spay WHERE F_SpayL_SalesID = sales_id AND F_SpayIsActive = "Y");
IF paid_total IS NULL THEN SET paid_total = 0; END IF;

UPDATE l_sales
SET L_SalesPaid = paid_total
WHERE L_SalesID = sales_id;
-- END TOTALIZE

-- IF (pay_amount - invoice_av) > 0.5 THEN
--     SELECT "ERR" status, "Jumlah Pembayaran Melebihi Tagihan !" message;
--     ROLLBACK;
-- ELSE
--     UPDATE l_sales SET L_SalesPaid = L_SalesPaid + pay_amount + pay_discamount + memo_amount - old_pay_amount
--     WHERE L_SalesID = sales_id;

--     UPDATE l_sales SET L_SalesUnpaid = L_SalesGrandTotal - L_SalesPaid WHERE L_SalesID = sales_id;

--     -- MEMOS
--     UPDATE f_spaymemo
--     SET F_SpayMemoIsActive = "O"
--     WHERE F_SpayMemoIsActive = "Y" AND F_SpayMemoF_SpayID = pay_id;

--     SET l = JSON_LENGTH(memos);
--     WHILE n < l DO
--         SET tmp = JSON_EXTRACT(memos, CONCAT("$[", n, "]"));
--         SET n = n+1;

--         SET memo_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.memo_id"));
--         SET memo_amnt = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.amount"));

--         SELECT F_MemoL_SalesID, F_MemoM_AccountID INTO memo_iv, memo_acc
--         FROM f_memo WHERE F_MEmoID = memo_id;

--         SET pay2_memo_id = (SELECT F_SpayMemoID FROM f_spaymemo WHERE F_SpayMemoF_SpayID = pay_id AND F_SpayMemoF_MemoID = memo_id AND F_SpayMemoIsActive = "O");
--         IF pay2_memo_id IS NULL THEN
--             INSERT INTO f_spaymemo(
--                 F_SpayMemoF_SpayID,
--                 F_SpayMemoL_SalesID,
--                 F_SpayMemoF_MemoID,
--                 F_SpayMemoM_CustomerID,
--                 F_SpayMemoAmount
--             )
--             SELECT pay_id, F_SpayL_SalesID, memo_id, F_SpayM_CustomerID, memo_amnt FROM f_spay WHERE F_SpayID = pay_id;
--             UPDATE f_memo SET F_MemoUsed = F_MemoUsed + memo_amnt WHERE F_MemoID = memo_id;
--         ELSE
--             SET old_memo_amnt = (SELECT F_SpayMemoAmount FROM f_spaymemo WHERE F_SpayMemoID = pay2_memo_id);

--             UPDATE f_spaymemo SET F_SpayMemoAmount = memo_amnt, F_SpayMemoIsActive = "Y" WHERE F_SpayMemoID = pay2_memo_id;
--             UPDATE f_memo SET F_MemoUsed = F_MemoUsed + memo_amnt - old_memo_amnt WHERE F_MemoID = memo_id;
--         END IF;

--         SET memo_unused = (SELECT F_MemoAmount - F_MemoUsed - F_MemoRefunded FROM f_memo WHERE F_MemoID = memo_id);
--         IF memo_unused < 0 THEN
--             SET n = 999;
--             SELECT "ERR" as status, "Jumlah KREDIT MEMO tidak mencukupi, silahkan dicek kembali !" as message;
--             ROLLBACK;
--         END IF;
--     END WHILE;

--     UPDATE f_memo
--     JOIN f_spaymemo ON F_SpayMemoF_SpayID = pay_id AND F_SpayMemoIsActive = "O" AND F_SpayMemoF_MemoID = F_MemoID
--     SET F_MemoUsed = F_MemoUsed - F_SpayMemoAmount;

--     UPDATE f_spaymemo SET F_SpayMemoIsActive = "N" WHERE F_SpayMemoF_SpayID = pay_id AND F_SpayMemoIsActive = "O";

--     SET pay_total = (SELECT F_SpayAmount + F_SpayDiscAmount + F_SpayMemoAmount FROM f_spay WHERE F_SpayID = pay_id);
--     UPDATE f_spay SET F_SpayTotal = pay_total WHERE F_SpayID = pay_id;

--     -- ACCOUNT
--     -- ACCOUNT SECTION
--     SET @ldg = (SELECT CONCAT("Penerimaan #", pay_number, " ", M_CustomerName) FROM l_sales JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID WHERE L_SalesID = sales_id);
--     SET adata = CONCAT(JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", pay_total, "ledger_note", @ldg), ",",
--             JSON_OBJECT("account", pay_credit_account, "debit", pay_amount, "credit", 0, "ledger_note", @ldg));

--     IF pay_discamount > 0 THEN
--         SET adata = CONCAT(adata, ",", JSON_OBJECT("account", pay_disc_account, "debit", pay_discamount, "credit", 0, "ledger_note", @ldg));
--     END IF;

--     IF memo_amount > 0 THEN
--         IF memo_acc <> 0 AND memo_iv = 0 THEN SET account_debt_id = (SELECT fn_master_get_account_id("ACC.INCOME.PREPAID")); END IF;
--         SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_debt_id, "debit", memo_amount, "credit", 0, "ledger_note", @ldg));
--     END IF;

--     SET adata = CONCAT("[", adata, "]");
--     CALL sp_journal_save_notrans_noreturn(journal_id, pay_date, pay_number, pay_note, adata, "J.11", pay_credit_account );

--     IF journal_id = 0 THEN
--         SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = pay_number AND (T_JournalIsActive = "Y" OR T_JournalIsActive = "T") ANd T_JournalType = "J.11");
--         UPDATE f_spay SET F_SpayT_JournalID = journal_id WHERE F_SpayID = pay_id;
--     END IF;

--     UPDATE t_journal SET T_JournalRefID = pay_id WHERE T_JournalID = journal_id;

--     SELECT "OK" as status, JSON_OBJECT("pay_id", pay_id, "pay_number", pay_number) data;
--     COMMIT;
-- END IF;

SELECT "OK" as status, JSON_OBJECT("pay_id", pay_id, "pay_number", pay_number) data;
COMMIT;

END;;
DELIMITER ;
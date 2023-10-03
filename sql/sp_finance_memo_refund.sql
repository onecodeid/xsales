BEGIN

DECLARE refund_date DATE;
DECLARE refund_number VARCHAR(25);
DECLARE memo_id INTEGER;
DECLARE customer_id INTEGER;
DECLARE refund_amount DOUBLE;
DECLARE refund_credit_account INTEGER;
DECLARE refund_debit_account INTEGER;
DECLARE refund_note TEXT;

DECLARE memo_av DOUBLE;
DECLARE old_refund_amount DOUBLE DEFAULT 0;

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

SET refund_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.refund_date'));
SET refund_amount = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.refund_amount'));
SET memo_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.memo_id'));
SET refund_credit_account = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.refund_credit_account'));
SET refund_debit_account = (SELECT `fn_master_get_account_id`('ACC.DEBT'));
SET refund_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.refund_note'));

IF refund_id = 0 THEN
    SET refund_number = (SELECT fn_numbering("FMRFN"));
    SET memo_av = (SELECT (F_MemoAmount - F_MemoUsed - F_MemoRefunded) FROM f_memo WHERE F_MemoID = memo_id);
    
    INSERT INTO f_memorefund(
        F_MemoRefundDate,
        F_MemoRefundNumber,
        F_MemoRefundF_MemoID,
        F_MemoRefundM_CustomerID,
        F_MemoRefundAmount,
        F_MemoRefundCreditM_AccountID,
        F_MemoRefundDebitM_AccountID,
        F_MemoRefundNote)
    SELECT refund_date, refund_number, memo_id, F_MemoM_CustomerID, refund_amount, refund_credit_account, refund_debit_account, refund_note
    FROM f_memo WHERE F_MemoID = memo_id;

    SET refund_id = (SELECT LAST_INSERT_ID());
ELSE
    SELECT F_MemoRefundNumber, F_MemoRefundF_MemoID INTO refund_number, memo_id 
    FROM f_memorefund WHERE F_MemoRefundID = refund_id;
    
    SET memo_av = (SELECT (F_MemoAmount - F_MemoUsed - F_MemoRefunded) FROM f_memo WHERE F_MemoID = memo_id);
    SET old_refund_amount = (SELECT F_MemoRefundAmount FROM f_memorefund WHERE F_MemoRefundID = refund_id);
    SET memo_av = memo_av + old_refund_amount;

    UPDATE f_memorefund
    SET F_MemoRefundDate = refund_date,
        F_MemoRefundAmount = refund_amount,
        F_MemoRefundCreditM_AccountID = refund_credit_account,
        F_MemoRefundDebitM_AccountID = refund_debit_account,
        F_MemoRefundNote = refund_note
    WHERE F_MemoRefundID = refund_id;
END IF;

IF refund_amount > memo_av THEN
    SELECT "ERR" status, "Jumlah Memo Tidak Mencukupi !" message;
    ROLLBACK;
ELSE
    UPDATE f_memo SET F_MemoRefunded = F_MemoRefunded + refund_amount - old_refund_amount
    WHERE F_MemoID = memo_id;

    SELECT "OK" as status, JSON_OBJECT("refund_id", refund_id, "refund_number", refund_number) data;
    COMMIT;
END IF;

END
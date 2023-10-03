DROP PROCEDURE `sp_finance_pay2_delete`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_pay2_delete` (IN `payid` int, IN `uid` int)
BEGIN

DECLARE invoice_id INTEGER;
DECLARE journal_id INTEGER;
DECLARE total DOUBLE;

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

SELECT F_Pay2L_InvoiceID, F_Pay2T_JournalID, F_Pay2Total INTO invoice_id, journal_id, total
FROM f_pay2 WHERE F_Pay2ID = payid;

UPDATE f_pay2 SET F_Pay2IsActive = "N" WHERE F_Pay2ID = payid;
UPDATE l_invoice SET L_InvoicePaid = L_InvoicePaid - total, L_InvoiceUnpaid = L_InvoiceUnpaid + total WHERE L_InvoiceID = invoice_id;
UPDATE t_journal SET T_JournalIsActive = "N" WHERE T_JournalID = journal_id;
UPDATE t_journaldetail SET T_JournalDetailIsActive = "N" WHERE T_JournalDetailT_JournalID = journal_id;

-- UPDATE MEMO
UPDATE f_memo
JOIN f_pay2memo ON F_Pay2MemoF_MemoID = F_MemoID AND F_Pay2MemoIsActive = "Y"
    AND F_Pay2MemoF_Pay2ID = payid
SET F_MemoUsed = F_MemoUsed - F_Pay2MemoAmount
WHERE F_MemoIsActive = "Y";

UPDATE f_pay2memo SET F_Pay2MemoIsActive = "N" WHERE F_Pay2MemoF_Pay2ID = payid;

SELECT "OK" status, JSON_OBJECT("pay_id", payid, "invoice_id", invoice_id, "amount", total) data;

COMMIT;

END;;
DELIMITER ;
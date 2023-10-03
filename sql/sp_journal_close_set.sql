BEGIN

DECLARE account_profitloss_id INTEGER;
DECLARE account_income_id INTEGER;
DECLARE journal_id INTEGER;
DECLARE adata TEXT;

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

IF what = 'PROFITLOSS' THEN
    SET account_profitloss_id = (SELECT fn_master_get_account_id("ACC.PROFITLOSS"));
    SET account_income_id = (SELECT fn_master_get_account_id("ACC.INCOME"));
    SET journal_id = (SELECT T_JournalCloseID FROM t_journalclose WHERE T_JournalCloseType = "J.90" AND T_JournalCloseIsActive = "Y" AND T_JournalCloseDate = pdate);

    IF journal_id IS NULL THEN SET journal_id = 0; END IF;
    SET adata = CONCAT("[", JSON_OBJECT("account", account_income_id, "debit", amount, "credit", 0), ",",
            JSON_OBJECT("account", account_profitloss_id, "debit", 0, "credit", amount), "]");

    CALL sp_journal_close_save_notrans(journal_id, pdate, '', 'Ikhtisar Laba Rugi', adata, "J.90", account_profitloss_id );
    IF journal_id = 0 THEN
        SET journal_id = (SELECT MAX(T_JournalCloseID) FROM t_journalclose 
            WHERE T_JournalCloseReceipt = '' AND T_JournalCloseIsActive = "Y" ANd T_JournalCloseType = "J.90" AND T_JournalCloseDate = pdate);
    END IF;

    SELECT "OK" status, JSON_OBJECT("journal_id", journal_id) data;
END IF;

COMMIT;

END
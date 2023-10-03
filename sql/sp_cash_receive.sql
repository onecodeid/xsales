BEGIN

-- DECLARE journaldate DATE;
-- DECLARE journalreceipt VARCHAR(100);
-- DECLARE journalnote VARCHAR(255);
-- DECLARE journaltype VARCHAR(10);

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

-- SET journaldate = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.journal_date'));
-- SET journalreceipt = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.journal_receipt'));
-- SET journalnote = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.journal_note'));
-- SET journaltype = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.journal_type'));

CALL sp_journal_save_notrans(journalid, journaldate, journalreceipt, journalnote, jdata, journaltype, accountid);

COMMIT;

END
BEGIN

DECLARE jpost CHAR(1);

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

SET jpost = (SELECT T_JournalPost FROM t_journal WHERE T_JournalID = journalid);
IF jpost = "Y" THEN
	SELECT "ERR" as status, "Journal tersebut telah diposting, tidak bisa menghapus";
ELSE
	UPDATE t_journal SET T_JournalIsActive = "N" WHERE T_JournalID = journalid;
	UPDATE t_journaldetail SET T_JournalDetailIsActive = "N" WHERE T_JournalDetailT_JournalID =journalid;
	
	SELECT "OK" as status, JSON_OBJECT("journal_id", journalid) as data;
	COMMIT;
END IF;

END
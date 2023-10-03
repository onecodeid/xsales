BEGIN

DECLARE bill_id INTEGER;
DECLARE used DOUBLE;
DECLARE journal_id INTEGER;

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

SET bill_id = (SELECT F_BillDpF_BillID FROM f_billdp WHERE F_BillDpID = dpid);
SET used = (SELECT F_BillDpUsed FROM f_billdp WHERE F_BillDpID = dpid);
SET journal_id = (SELECT F_BillDpT_JournalID FROM f_billdp WHERE F_BillDpID = dpid);

IF bill_id = 0 AND used = 0 THEN
    UPDATE f_billdp
    SET F_BillDpIsActive = "N"
    WHERE F_BillDpID = dpid;

    UPDATE t_journal
    SET T_JournalIsActive = "N"
    WHERE T_JournalID = journal_id;

    UPDATE t_journaldetail
    SET T_JournalDetailIsActive = "N"
    WHERE T_JournalDetailT_JournalID = journal_id;

    SELECT "OK" status, JSON_OBJECT("dp_id", dpid) data;
    COMMIT;
ELSE
    SELECT "ERR" status, "Uang muka tersebut telah terpakai, tidak bisa dihapus !" message;
    ROLLBACK;
END IF;

END
BEGIN

DECLARE used DOUBLE;
DECLARE fully_used CHAR(1);
DECLARE journal_id INTEGER;
DECLARE acc CHAR(1);

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

SELECT F_PaymentDpUsed, F_PaymentDpFullyUsed, F_PaymentDpT_JournalID, F_PaymentDpAcc
INTO used, fully_used, journal_id, acc
FROM f_paymentdp WHERE F_PaymentDpID = dpid;

IF used > 0 OR fully_used = "Y" THEN
    SELECT "ERR" status, "DP telah digunakan, tidak bisa dihapus" message;
    ROLLBACK;
ELSE
    UPDATE f_paymentdp
    SET F_PaymentDpIsActive = "N", F_PaymentDpUserID = uid
    WHERE F_PaymentDpID = dpid;

    IF acc = "Y" AND journal_id <> 0 THEN
        UPDATE t_journaldetail SET T_JournalDetailIsActive = "N" WHERE T_JournalDetailT_JournalID = journal_id;
        UPDATE t_journal SET T_JournalIsActive = "N" WHERE T_JournalID = journal_id;
    END IF;

    SELECT "OK" as status, JSON_OBJECT("dp_id", dpid) as data;
    COMMIT;
END IF;

END
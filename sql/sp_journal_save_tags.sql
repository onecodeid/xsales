DROP PROCEDURE `sp_journal_save_tags`;
DELIMITER ;;
CREATE PROCEDURE `sp_journal_save_tags` (IN `journalid` int, IN `journaldate` date, IN `journalreceipt` varchar(50), IN `journalnote` varchar(2000), IN `jdata` text, IN `tags` TEXT)
BEGIN

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

CALL sp_journal_save_tags_notrans(journalid, journaldate, journalreceipt, journalnote, jdata, "J.01", 0, tags);

COMMIT;

END;;
DELIMITER ;
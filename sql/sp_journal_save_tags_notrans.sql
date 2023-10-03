DROP PROCEDURE `sp_journal_save_tags_notrans`;
DELIMITER ;;
CREATE PROCEDURE `sp_journal_save_tags_notrans` (IN `journalid` int, IN `journaldate` date, IN `journalreceipt` varchar(50), IN `journalnote` varchar(2000), IN `jdata` text, IN `journaltype` char(4), IN `mainaccountid` int, IN `tags` varchar(255))
BEGIN

DECLARE tmp VARCHAR(2000);
DECLARE accountid INTEGER DEFAULT 0;
DECLARE accountxid INTEGER DEFAULT 0;
DECLARE debit DOUBLE DEFAULT 0;
DECLARE credit DOUBLE DEFAULT 0;
DECLARE total_debit DOUBLE DEFAULT 0;
DECLARE total_credit DOUBLE DEFAULT 0;
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE detail_id INTEGER;
DECLARE account_ids VARCHAR(50) DEFAULT "";
DECLARE ledger_note VARCHAR(255);

DECLARE j_typeid INTEGER;
DECLARE j_numbercode VARCHAR(25);
DECLARE j_number VARCHAR(25);
DECLARE acc_type INTEGER;

DECLARE tag_id INTEGER;
DECLARE tag_name VARCHAR(50);
DECLARE jtag_id INTEGER;
DECLARE tmp_tags VARCHAR(1000) DEFAULT "[";

IF tags IS NULL OR tags = "" THEN SET tags = "[]"; END IF;

-- TAGS
-- SET l = JSON_LENGTH(tags);
-- SET n = 0;
-- WHILE n < l DO
--     SET tmp = JSON_EXTRACT(tags, CONCAT('$[', n, ']'));
--     SET tag_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.tag_id'));
-- 	SET tag_name = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.tag_name'));

-- 	IF tag_id = 0 THEN
-- 		INSERT INTO m_tag(M_TagType, M_TagName) SELECT "TAG.USER", tag_name;
-- 		SET tag_id = (SELECT LAST_INSERT_ID());
-- 	END IF;

-- 	SET tmp_tags = CONCAT(tmp_tags, JSON_OBJECT("tag_id", tag_id, "tag_name", tag_name));
--     SET n = n + 1;
-- END WHILE;
-- SET tags = CONCAT(tmp_tags, "]");
-- END OF TAGS

IF journalid = 0 THEN
	INSERT INTO t_journal(T_JournalDate,
		T_JournalNumber,
		T_JournalNote,
		T_JournalReceipt,
		T_JournalType,
        T_JournalMainM_AccountID,
        T_JournalTags)
	SELECT journaldate, fn_numbering("J"), journalnote, journalreceipt, journaltype, mainaccountid, tags;
	
	SET journalid = (SELECT LAST_INSERT_ID());
ELSE
	UPDATE t_journal
	SET T_JournalDate=journaldate ,
	T_JournalNote=journalnote,
	T_JournalReceipt=journalreceipt,
    T_JournalTags = tags
	WHERE T_JournalID = journalid;
END IF;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
	SET accountid = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.account'));
	SET accountxid = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.accountx'));
	SET credit = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.credit'));
	SET debit = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.debit'));
	
	SET detail_id = (SELECT T_JournalDetailID FROM t_journaldetail WHERE T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y" AND T_JournalDetailM_AccountID = accountid);
	IF accountxid IS NULL THEN SET accountxid = 0; END IF;

	SET ledger_note = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.ledger_note'));
	IF ledger_note IS NULL THEN
        SET ledger_note = "";
        IF accountxid <> 0 THEN
                SET ledger_note = (SELECT JSON_ARRAY(m_accountname, M_JournalTypeName, T_JournalNumber) FROM t_journal
                        JOIN m_account ON m_accountid = accountxid
                        JOIN m_journaltype ON M_JournalTypeCode = journaltype AND M_JournalTypeIsActive = "Y"
                        WHERE t_journalid = journalid);
        END IF;
	END IF;
	
	IF detail_id IS NULL THEN
		INSERT INTO t_journaldetail(
			T_JournalDetailT_JournalID,
			T_JournalDetailM_AccountID,
			T_JournalDetailDebit,
			T_JournalDetailCredit,
			T_JournalDetailLedgerNote
		)
		SELECT journalid, accountid, debit, credit, ledger_note;
		SET detail_id = (SELECT LAST_INSERT_ID());
	ELSE
		UPDATE t_journaldetail
		SET T_JournalDetailCredit = credit, T_JournalDetailDebit = debit, 
			T_JournalDetailLedgerNote = ledger_note
		WHERE T_JournalDetailID = detail_id;
	END IF;
	
	IF account_ids = "" THEN SET account_ids = accountid; ELSE SET account_ids = CONCAT(account_ids,",",accountid); END IF;
	
	SET total_credit = total_credit + credit;
	SET total_debit = total_debit + debit;
	SET n = n+1;
END WHILE;

UPDATE t_journaldetail SET T_JournalDetailIsActive = "N" WHERE NOT FIND_IN_SET(T_JournalDetailM_AccountID, account_ids) AND T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y";
UPDATE t_journal SET T_JournalCredit = total_credit, T_JournalDebit = total_debit WHERE T_JournalID = journalid;

-- JOURNAL TAGS
-- UPDATE t_journaltag SET T_JournalTagIsActive = "O" WHERE T_JournalTagT_JournalID = journalid AND T_JournalTagIsActive = "Y";
-- -- SET l = JSON_LENGTH(tags);
-- SET n = 0;
-- WHILE n < l DO
--     SET tmp = JSON_EXTRACT(tags, CONCAT('$[', n, ']'));
--     SET tag_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.tag_id'));
-- 	SET tag_name = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.tag_name'));

--     SET jtag_id = (SELECT T_JournalTagID FROM T_JournalTag WHERE T_JournalTagT_JournalID = journalid AND T_JournalTagIsActive = "O" AND T_JournalTagM_TagID = tag_id);
    
--     IF jtag_id IS NULL THEN
--         INSERT INTO t_journaltag(T_JournalTagM_TagID, T_JournalTagT_JournalID)
--         SELECT tag_id, journalid;
--     ELSE
--         UPDATE t_journaltag SET T_JournalTagIsActive = "Y" WHERE T_JournalTagID = jtag_id;
--     END IF;

--     SET n = n + 1;
-- END WHILE;
-- UPDATE t_journaltag SET T_JournalTagIsActive = "N" WHERE T_JournalTagT_JournalID = journalid AND T_JournalTagIsActive = "O";
-- END OF JOURNAL TAGS

-- CASHFLOW
CALL sp_journal_cashflow(journalid);

SELECT "OK" status, JSON_OBJECT("journal_id", journalid, "total_credit", credit, "total_debit", debit) data;

END;;
DELIMITER ;
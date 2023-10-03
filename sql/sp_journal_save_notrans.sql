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

IF journalid = 0 THEN
	INSERT INTO t_journal(T_JournalDate,
		T_JournalNumber,
		T_JournalNote,
		T_JournalReceipt,
		T_JournalType,
        T_JournalMainM_AccountID)
	SELECT journaldate, fn_numbering("J"), journalnote, journalreceipt, journaltype, mainaccountid;
	
	SET journalid = (SELECT LAST_INSERT_ID());
ELSE
	UPDATE t_journal
	SET T_JournalDate=journaldate ,
	T_JournalNote=journalnote,
	T_JournalReceipt=journalreceipt
	WHERE T_JournalID = journalid;
END IF;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
	SET accountid = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.account'));
	SET accountxid = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.accountx'));
	SET credit = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.credit'));
	SET debit = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.debit'));
	
	SET detail_id = (SELECT T_JournalDetailID FROM t_journaldetail WHERE T_JournalDetailT_JournalID = journalid 
		AND (T_JournalDetailIsActive = "Y" OR T_JournalDetailIsActive = "T") AND T_JournalDetailM_AccountID = accountid LIMIT 1);
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

UPDATE t_journaldetail SET T_JournalDetailIsActive = "N" WHERE NOT FIND_IN_SET(T_JournalDetailM_AccountID, account_ids) 
	AND T_JournalDetailT_JournalID = journalid AND (T_JournalDetailIsActive = "Y" OR T_JournalDetailIsActive = "T");
UPDATE t_journal SET T_JournalCredit = total_credit, T_JournalDebit = total_debit WHERE T_JournalID = journalid;

-- CASHFLOW
CALL sp_journal_cashflow(journalid);

SELECT "OK" status, JSON_OBJECT("journal_id", journalid, "total_credit", credit, "total_debit", debit) data;

END
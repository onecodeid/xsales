DELIMITER ;;
CREATE PROCEDURE `sp_journal_save_notrans_adminer_5ffaafc303632` (IN `journalid` int, IN `journaldate` date, IN `journalreceipt` varchar(50), IN `journalnote` varchar(2000), IN `jdata` text, IN `journaltype` char(4), IN `mainaccountid` int)
BEGIN

DECLARE tmp VARCHAR(2000);
DECLARE accountid INTEGER DEFAULT 0;
DECLARE debit DOUBLE DEFAULT 0;
DECLARE credit DOUBLE DEFAULT 0;
DECLARE total_debit DOUBLE DEFAULT 0;
DECLARE total_credit DOUBLE DEFAULT 0;
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE detail_id INTEGER;
DECLARE account_ids VARCHAR(50) DEFAULT "";

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
	SET credit = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.credit'));
	SET debit = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.debit'));
	
	SET detail_id = (SELECT T_JournalDetailID FROM t_journaldetail WHERE T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y" AND T_JournalDetailM_AccountID = accountid);
	
	IF detail_id IS NULL THEN
		INSERT INTO t_journaldetail(
			T_JournalDetailT_JournalID,
			T_JournalDetailM_AccountID,
			T_JournalDetailDebit,
			T_JournalDetailCredit
		)
		SELECT journalid, accountid, debit, credit;
		SET detail_id = (SELECT LAST_INSERT_ID());
	ELSE
		UPDATE t_journaldetail
		SET T_JournalDetailCredit = credit, T_JournalDetailDebit = debit
		WHERE T_JournalDetailID = detail_id;
	END IF;
	
	IF account_ids = "" THEN SET account_ids = accountid; ELSE SET account_ids = CONCAT(account_ids,",",accountid); END IF;
	
	SET total_credit = total_credit + credit;
	SET total_debit = total_debit + debit;
	SET n = n+1;
END WHILE;

UPDATE t_journaldetail SET T_JournalDetailIsActive = "N" WHERE NOT FIND_IN_SET(T_JournalDetailM_AccountID, account_ids) AND T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y";
UPDATE t_journal SET T_JournalCredit = total_credit, T_JournalDebit = total_debit WHERE T_JournalID = journalid;

SELECT "OK" status, JSON_OBJECT("journal_id", journalid, "total_credit", credit, "total_debit", debit) data;

END
;;
DELIMITER ;
DROP PROCEDURE `sp_journal_save_notrans_adminer_5ffaafc303632`;
DROP PROCEDURE `sp_journal_save_notrans`;
DELIMITER ;;
CREATE PROCEDURE `sp_journal_save_notrans` (IN `journalid` int, IN `journaldate` date, IN `journalreceipt` varchar(50), IN `journalnote` varchar(2000), IN `jdata` text, IN `journaltype` char(4), IN `mainaccountid` int)
BEGIN

DECLARE tmp VARCHAR(2000);
DECLARE accountid INTEGER DEFAULT 0;
DECLARE debit DOUBLE DEFAULT 0;
DECLARE credit DOUBLE DEFAULT 0;
DECLARE total_debit DOUBLE DEFAULT 0;
DECLARE total_credit DOUBLE DEFAULT 0;
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE detail_id INTEGER;
DECLARE account_ids VARCHAR(50) DEFAULT "";

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
	SET credit = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.credit'));
	SET debit = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.debit'));
	
	SET detail_id = (SELECT T_JournalDetailID FROM t_journaldetail WHERE T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y" AND T_JournalDetailM_AccountID = accountid);
	
	IF detail_id IS NULL THEN
		INSERT INTO t_journaldetail(
			T_JournalDetailT_JournalID,
			T_JournalDetailM_AccountID,
			T_JournalDetailDebit,
			T_JournalDetailCredit
		)
		SELECT journalid, accountid, debit, credit;
		SET detail_id = (SELECT LAST_INSERT_ID());
	ELSE
		UPDATE t_journaldetail
		SET T_JournalDetailCredit = credit, T_JournalDetailDebit = debit
		WHERE T_JournalDetailID = detail_id;
	END IF;
	
	IF account_ids = "" THEN SET account_ids = accountid; ELSE SET account_ids = CONCAT(account_ids,",",accountid); END IF;
	
	SET total_credit = total_credit + credit;
	SET total_debit = total_debit + debit;
	SET n = n+1;
END WHILE;

UPDATE t_journaldetail SET T_JournalDetailIsActive = "N" WHERE NOT FIND_IN_SET(T_JournalDetailM_AccountID, account_ids) AND T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y";
UPDATE t_journal SET T_JournalCredit = total_credit, T_JournalDebit = total_debit WHERE T_JournalID = journalid;

SELECT "OK" status, JSON_OBJECT("journal_id", journalid, "total_credit", credit, "total_debit", debit) data;

END
;;
DELIMITER ;

DELIMITER ;;
CREATE PROCEDURE `sp_journal_save_adminer_5ffab07f6d7c6` (IN `journalid` int, IN `journaldate` date, IN `journalreceipt` varchar(50), IN `journalnote` varchar(2000), IN `jdata` text)
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

CALL sp_journal_save_notrans(journalid, journaldate, journalreceipt, journalnote, jdata, "J.01", 0);

COMMIT;

END
;;
DELIMITER ;
DROP PROCEDURE `sp_journal_save_adminer_5ffab07f6d7c6`;
DROP PROCEDURE `sp_journal_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_journal_save` (IN `journalid` int, IN `journaldate` date, IN `journalreceipt` varchar(50), IN `journalnote` varchar(2000), IN `jdata` text)
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

CALL sp_journal_save_notrans(journalid, journaldate, journalreceipt, journalnote, jdata, "J.01", 0);

COMMIT;

END
;;
DELIMITER ;

DELIMITER ;;
CREATE PROCEDURE `sp_payment2_save_adminer_5ffab176942a1` (IN `paymentid` int, IN `hdata` text, IN `jdata` text)
BEGIN

DECLARE payment_date DATE;
DECLARE payment_number VARCHAR(25);
DECLARE payment_total DOUBLE DEFAULT 0;
DECLARE payment_note VARCHAR(255);
DECLARE payment_uid INTEGER;
DECLARE payment_customer INTEGER;

DECLARE giro_number VARCHAR(50);
DECLARE giro_date DATE;
DECLARE transfer_date DATE;
DECLARE bank_id INTEGER;
DECLARE bank_account_id INTEGER;
DECLARE payment_type INTEGER;
DECLARE account_id INTEGER;
DECLARE account_payable_id INTEGER;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_invoice INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_type INTEGER;
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(100) DEFAULT "";
DECLARE total DOUBLE DEFAULT 0;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4);	
DECLARE adata VARCHAR(500);


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

SET payment_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.date'));
SET payment_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.note'));
SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
SET payment_uid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.uid'));

SET giro_number = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_number'));
SET giro_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_date'));
SET transfer_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.transfer_date'));
SET bank_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_id'));
SET bank_account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_account_id'));
SET account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_id'));
SET payment_type = (SELECT M_AccountMapReffID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET journal_type = (SELECT M_AccountMapJournalType FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET account_payable_id = (SELECT M_AccountMapM_AccountID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapType = "SAL.01");

IF giro_number IS NULL THEN SET giro_number = ""; END IF;
IF bank_id IS NULL THEN SET bank_id = 0; END IF;
IF bank_account_id IS NULL THEN SET bank_account_id = 0; END IF;

IF paymentid = 0 THEN	
	SET payment_number = fn_numbering('PAY');
	SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
	INSERT INTO f_payment2(F_Payment2Date,
		F_Payment2Number,
		F_Payment2A_CustomerID,
		F_Payment2Total,
		F_Payment2Note,
		F_Payment2UserID,
		F_Payment2M_PaymentTypeID,
		F_Payment2A_BankAccountID,
		F_Payment2M_BankID,
		F_Payment2GiroDate,
		F_Payment2GiroNumber,
		F_Payment2TransferDate)
	SELECT payment_date, payment_number, payment_customer, 0, payment_note, payment_uid, payment_type, bank_account_id, bank_id, giro_date, giro_number, transfer_date;
	
	SET paymentid = (SELECT LAST_INSERT_ID());
ELSE
	UPDATE f_payment2
	SET F_Payment2Date = payment_date, F_Payment2Note = payment_note, F_Payment2A_BankAccountID = bank_account_id,
		F_Payment2M_BankID = bank_id,
		F_Payment2GiroDate = giro_date,
		F_Payment2GiroNumber = giro_number,
		F_Payment2TransferDate = transfer_date
	WHERE F_Payment2ID = paymentid;
	
	SET journal_id = (SELECT F_Payment2T_JournalID FROM f_payment2 WHERE F_Payment2ID = paymentid);
	SET payment_number = (SELECT F_Payment2Number FROM f_payment2 WHERE F_Payment2ID = paymentid);
END IF;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
	SET d_invoice = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.invoice'));
	SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.amount'));
	SET d_id = (SELECT F_Payment2DetailID FROM f_payment2detail WHERE F_Payment2DetailIsActive = "Y" AND F_Payment2DetailF_Payment2ID = paymentid AND F_Payment2DetailL_InvoiceID = d_invoice);
	
	IF d_id IS NULL THEN
		INSERT INTO f_payment2detail(F_Payment2DetailF_Payment2ID, F_Payment2DetailL_InvoiceID, F_Payment2DetailAmount)
		SELECT paymentid, d_invoice, d_amount;
		
		SET d_id = (SELECT LAST_INSERT_ID());
	ELSE
		UPDATE f_payment2detail
		SET F_Payment2DetailAmount = d_amount
		WHERE F_Payment2DetailID = d_id;
	END IF;
	
	IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
	SET n = n + 1;
	
	SET total = total + d_amount;
END WHILE;

UPDATE f_payment2detail
SET F_Payment2DetailIsActive = "N"
WHERE F_Payment2DetailF_Payment2ID = paymentid AND NOT FIND_IN_SET(F_Payment2DetailID, d_ids) AND F_Payment2DetailIsActive = "Y";

UPDATE f_payment2
SET F_Payment2Total = total WHERe F_Payment2ID = paymentid;

SET adata = JSON_ARRAY(JSON_OBJECT("account", account_id, "debit", total, "credit", 0),JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", total));
CALL sp_journal_save_notrans(journal_id, payment_date, payment_number, payment_note, adata, journal_type, account_id );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
	UPDATE f_payment2 SET F_Payment2T_JournalID = journal_id WHERE F_Payment2ID = paymentid;
END IF;

SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid) as data;

COMMIT;

END
;;
DELIMITER ;
DROP PROCEDURE `sp_payment2_save_adminer_5ffab176942a1`;
DROP PROCEDURE `sp_payment2_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_payment2_save` (IN `paymentid` int, IN `hdata` text, IN `jdata` text)
BEGIN

DECLARE payment_date DATE;
DECLARE payment_number VARCHAR(25);
DECLARE payment_total DOUBLE DEFAULT 0;
DECLARE payment_note VARCHAR(255);
DECLARE payment_uid INTEGER;
DECLARE payment_customer INTEGER;

DECLARE giro_number VARCHAR(50);
DECLARE giro_date DATE;
DECLARE transfer_date DATE;
DECLARE bank_id INTEGER;
DECLARE bank_account_id INTEGER;
DECLARE payment_type INTEGER;
DECLARE account_id INTEGER;
DECLARE account_payable_id INTEGER;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_invoice INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_type INTEGER;
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(100) DEFAULT "";
DECLARE total DOUBLE DEFAULT 0;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4);	
DECLARE adata VARCHAR(500);


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

SET payment_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.date'));
SET payment_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.note'));
SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
SET payment_uid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.uid'));

SET giro_number = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_number'));
SET giro_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_date'));
SET transfer_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.transfer_date'));
SET bank_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_id'));
SET bank_account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_account_id'));
SET account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_id'));
SET payment_type = (SELECT M_AccountMapReffID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET journal_type = (SELECT M_AccountMapJournalType FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET account_payable_id = (SELECT M_AccountMapM_AccountID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapType = "SAL.01");

IF giro_number IS NULL THEN SET giro_number = ""; END IF;
IF bank_id IS NULL THEN SET bank_id = 0; END IF;
IF bank_account_id IS NULL THEN SET bank_account_id = 0; END IF;

IF paymentid = 0 THEN	
	SET payment_number = fn_numbering('PAY');
	SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
	INSERT INTO f_payment2(F_Payment2Date,
		F_Payment2Number,
		F_Payment2A_CustomerID,
		F_Payment2Total,
		F_Payment2Note,
		F_Payment2UserID,
		F_Payment2M_PaymentTypeID,
		F_Payment2A_BankAccountID,
		F_Payment2M_BankID,
		F_Payment2GiroDate,
		F_Payment2GiroNumber,
		F_Payment2TransferDate)
	SELECT payment_date, payment_number, payment_customer, 0, payment_note, payment_uid, payment_type, bank_account_id, bank_id, giro_date, giro_number, transfer_date;
	
	SET paymentid = (SELECT LAST_INSERT_ID());
ELSE
	UPDATE f_payment2
	SET F_Payment2Date = payment_date, F_Payment2Note = payment_note, F_Payment2A_BankAccountID = bank_account_id,
		F_Payment2M_BankID = bank_id,
		F_Payment2GiroDate = giro_date,
		F_Payment2GiroNumber = giro_number,
		F_Payment2TransferDate = transfer_date
	WHERE F_Payment2ID = paymentid;
	
	SET journal_id = (SELECT F_Payment2T_JournalID FROM f_payment2 WHERE F_Payment2ID = paymentid);
	SET payment_number = (SELECT F_Payment2Number FROM f_payment2 WHERE F_Payment2ID = paymentid);
END IF;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
	SET d_invoice = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.invoice'));
	SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.amount'));
	SET d_id = (SELECT F_Payment2DetailID FROM f_payment2detail WHERE F_Payment2DetailIsActive = "Y" AND F_Payment2DetailF_Payment2ID = paymentid AND F_Payment2DetailL_InvoiceID = d_invoice);
	
	IF d_id IS NULL THEN
		INSERT INTO f_payment2detail(F_Payment2DetailF_Payment2ID, F_Payment2DetailL_InvoiceID, F_Payment2DetailAmount)
		SELECT paymentid, d_invoice, d_amount;
		
		SET d_id = (SELECT LAST_INSERT_ID());
	ELSE
		UPDATE f_payment2detail
		SET F_Payment2DetailAmount = d_amount
		WHERE F_Payment2DetailID = d_id;
	END IF;
	
	IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
	SET n = n + 1;
	
	SET total = total + d_amount;
END WHILE;

UPDATE f_payment2detail
SET F_Payment2DetailIsActive = "N"
WHERE F_Payment2DetailF_Payment2ID = paymentid AND NOT FIND_IN_SET(F_Payment2DetailID, d_ids) AND F_Payment2DetailIsActive = "Y";

UPDATE f_payment2
SET F_Payment2Total = total WHERe F_Payment2ID = paymentid;

SET adata = JSON_ARRAY(JSON_OBJECT("account", account_id, "debit", total, "credit", 0),JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", total));
CALL sp_journal_save_notrans(journal_id, payment_date, payment_number, payment_note, adata, journal_type, account_id );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
	UPDATE f_payment2 SET F_Payment2T_JournalID = journal_id WHERE F_Payment2ID = paymentid;
END IF;

SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid) as data;

COMMIT;

END
;;
DELIMITER ;

DELIMITER ;;
CREATE PROCEDURE `sp_payment2_save_adminer_5ffb150102511` (IN `paymentid` int, IN `hdata` text, IN `jdata` text)
BEGIN

DECLARE payment_date DATE;
DECLARE payment_number VARCHAR(25);
DECLARE payment_total DOUBLE DEFAULT 0;
DECLARE payment_note VARCHAR(255);
DECLARE payment_uid INTEGER;
DECLARE payment_customer INTEGER;

DECLARE giro_number VARCHAR(50);
DECLARE giro_date DATE;
DECLARE transfer_date DATE;
DECLARE bank_id INTEGER;
DECLARE bank_account_id INTEGER;
DECLARE payment_type INTEGER;
DECLARE account_id INTEGER;
DECLARE account_payable_id INTEGER;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_invoice INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_type INTEGER;
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(100) DEFAULT "";
DECLARE total DOUBLE DEFAULT 0;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4);	
DECLARE adata VARCHAR(500);


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

SET payment_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.date'));
SET payment_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.note'));
SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
SET payment_uid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.uid'));

SET giro_number = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_number'));
SET giro_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_date'));
SET transfer_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.transfer_date'));
SET bank_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_id'));
SET bank_account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_account_id'));
SET account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_id'));
SET payment_type = (SELECT M_AccountMapReffID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET journal_type = (SELECT M_AccountMapJournalType FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET account_payable_id = (SELECT M_AccountMapM_AccountID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapType = "SAL.01");

IF giro_number IS NULL THEN SET giro_number = ""; END IF;
IF bank_id IS NULL THEN SET bank_id = 0; END IF;
IF bank_account_id IS NULL THEN SET bank_account_id = 0; END IF;

IF paymentid = 0 THEN	
	SET payment_number = fn_numbering('PAY');
	SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
	INSERT INTO f_payment2(F_Payment2Date,
		F_Payment2Number,
		F_Payment2A_CustomerID,
		F_Payment2Total,
		F_Payment2Note,
		F_Payment2UserID,
		F_Payment2M_PaymentTypeID,
		F_Payment2A_BankAccountID,
		F_Payment2M_BankID,
		F_Payment2GiroDate,
		F_Payment2GiroNumber,
		F_Payment2TransferDate)
	SELECT payment_date, payment_number, payment_customer, 0, payment_note, payment_uid, payment_type, bank_account_id, bank_id, giro_date, giro_number, transfer_date;
	
	SET paymentid = (SELECT LAST_INSERT_ID());
ELSE
	UPDATE f_payment2
	SET F_Payment2Date = payment_date, F_Payment2Note = payment_note, F_Payment2A_BankAccountID = bank_account_id,
		F_Payment2M_BankID = bank_id,
		F_Payment2GiroDate = giro_date,
		F_Payment2GiroNumber = giro_number,
		F_Payment2TransferDate = transfer_date
	WHERE F_Payment2ID = paymentid;
	
	SET journal_id = (SELECT F_Payment2T_JournalID FROM f_payment2 WHERE F_Payment2ID = paymentid);
	SET payment_number = (SELECT F_Payment2Number FROM f_payment2 WHERE F_Payment2ID = paymentid);
END IF;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
	SET d_invoice = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.invoice'));
	SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.amount'));
	SET d_id = (SELECT F_Payment2DetailID FROM f_payment2detail WHERE F_Payment2DetailIsActive = "Y" AND F_Payment2DetailF_Payment2ID = paymentid AND F_Payment2DetailL_InvoiceID = d_invoice);
	
	IF d_id IS NULL THEN
		INSERT INTO f_payment2detail(F_Payment2DetailF_Payment2ID, F_Payment2DetailL_InvoiceID, F_Payment2DetailAmount)
		SELECT paymentid, d_invoice, d_amount;
		
		SET d_id = (SELECT LAST_INSERT_ID());
	ELSE
		UPDATE f_payment2detail
		SET F_Payment2DetailAmount = d_amount
		WHERE F_Payment2DetailID = d_id;
	END IF;
	
	IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
	SET n = n + 1;
	
	SET total = total + d_amount;
END WHILE;

UPDATE f_payment2detail
SET F_Payment2DetailIsActive = "N"
WHERE F_Payment2DetailF_Payment2ID = paymentid AND NOT FIND_IN_SET(F_Payment2DetailID, d_ids) AND F_Payment2DetailIsActive = "Y";

UPDATE f_payment2
SET F_Payment2Total = total WHERe F_Payment2ID = paymentid;

SET adata = JSON_ARRAY(JSON_OBJECT("account", account_id, "debit", total, "credit", 0),JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", total));
CALL sp_journal_save_notrans(journal_id, payment_date, payment_number, payment_note, adata, journal_type );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
	UPDATE f_payment2 SET F_Payment2T_JournalID = journal_id WHERE F_Payment2ID = paymentid;
END IF;

SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid) as data;

COMMIT;

END
;;
DELIMITER ;
DROP PROCEDURE `sp_payment2_save_adminer_5ffb150102511`;
DROP PROCEDURE `sp_payment2_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_payment2_save` (IN `paymentid` int, IN `hdata` text, IN `jdata` text)
BEGIN

DECLARE payment_date DATE;
DECLARE payment_number VARCHAR(25);
DECLARE payment_total DOUBLE DEFAULT 0;
DECLARE payment_note VARCHAR(255);
DECLARE payment_uid INTEGER;
DECLARE payment_customer INTEGER;

DECLARE giro_number VARCHAR(50);
DECLARE giro_date DATE;
DECLARE transfer_date DATE;
DECLARE bank_id INTEGER;
DECLARE bank_account_id INTEGER;
DECLARE payment_type INTEGER;
DECLARE account_id INTEGER;
DECLARE account_payable_id INTEGER;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_invoice INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_type INTEGER;
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(100) DEFAULT "";
DECLARE total DOUBLE DEFAULT 0;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4);	
DECLARE adata VARCHAR(500);


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

SET payment_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.date'));
SET payment_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.note'));
SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
SET payment_uid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.uid'));

SET giro_number = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_number'));
SET giro_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_date'));
SET transfer_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.transfer_date'));
SET bank_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_id'));
SET bank_account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_account_id'));
SET account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_id'));
SET payment_type = (SELECT M_AccountMapReffID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET journal_type = (SELECT M_AccountMapJournalType FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET account_payable_id = (SELECT M_AccountMapM_AccountID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapType = "SAL.01");

IF giro_number IS NULL THEN SET giro_number = ""; END IF;
IF bank_id IS NULL THEN SET bank_id = 0; END IF;
IF bank_account_id IS NULL THEN SET bank_account_id = 0; END IF;

IF paymentid = 0 THEN	
	SET payment_number = fn_numbering('PAY');
	SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
	INSERT INTO f_payment2(F_Payment2Date,
		F_Payment2Number,
		F_Payment2A_CustomerID,
		F_Payment2Total,
		F_Payment2Note,
		F_Payment2UserID,
		F_Payment2M_PaymentTypeID,
		F_Payment2A_BankAccountID,
		F_Payment2M_BankID,
		F_Payment2GiroDate,
		F_Payment2GiroNumber,
		F_Payment2TransferDate)
	SELECT payment_date, payment_number, payment_customer, 0, payment_note, payment_uid, payment_type, bank_account_id, bank_id, giro_date, giro_number, transfer_date;
	
	SET paymentid = (SELECT LAST_INSERT_ID());
ELSE
	UPDATE f_payment2
	SET F_Payment2Date = payment_date, F_Payment2Note = payment_note, F_Payment2A_BankAccountID = bank_account_id,
		F_Payment2M_BankID = bank_id,
		F_Payment2GiroDate = giro_date,
		F_Payment2GiroNumber = giro_number,
		F_Payment2TransferDate = transfer_date
	WHERE F_Payment2ID = paymentid;
	
	SET journal_id = (SELECT F_Payment2T_JournalID FROM f_payment2 WHERE F_Payment2ID = paymentid);
	SET payment_number = (SELECT F_Payment2Number FROM f_payment2 WHERE F_Payment2ID = paymentid);
END IF;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
	SET d_invoice = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.invoice'));
	SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.amount'));
	SET d_id = (SELECT F_Payment2DetailID FROM f_payment2detail WHERE F_Payment2DetailIsActive = "Y" AND F_Payment2DetailF_Payment2ID = paymentid AND F_Payment2DetailL_InvoiceID = d_invoice);
	
	IF d_id IS NULL THEN
		INSERT INTO f_payment2detail(F_Payment2DetailF_Payment2ID, F_Payment2DetailL_InvoiceID, F_Payment2DetailAmount)
		SELECT paymentid, d_invoice, d_amount;
		
		SET d_id = (SELECT LAST_INSERT_ID());
	ELSE
		UPDATE f_payment2detail
		SET F_Payment2DetailAmount = d_amount
		WHERE F_Payment2DetailID = d_id;
	END IF;
	
	IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
	SET n = n + 1;
	
	SET total = total + d_amount;
END WHILE;

UPDATE f_payment2detail
SET F_Payment2DetailIsActive = "N"
WHERE F_Payment2DetailF_Payment2ID = paymentid AND NOT FIND_IN_SET(F_Payment2DetailID, d_ids) AND F_Payment2DetailIsActive = "Y";

UPDATE f_payment2
SET F_Payment2Total = total WHERe F_Payment2ID = paymentid;

SET adata = JSON_ARRAY(JSON_OBJECT("account", account_id, "debit", total, "credit", 0),JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", total));
CALL sp_journal_save_notrans(journal_id, payment_date, payment_number, payment_note, adata, journal_type );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
	UPDATE f_payment2 SET F_Payment2T_JournalID = journal_id WHERE F_Payment2ID = paymentid;
END IF;

SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid) as data;

COMMIT;

END
;;
DELIMITER ;

DELIMITER ;;
CREATE PROCEDURE `sp_payment2_save_adminer_5ffb15938c402` (IN `paymentid` int, IN `hdata` text, IN `jdata` text)
BEGIN

DECLARE payment_date DATE;
DECLARE payment_number VARCHAR(25);
DECLARE payment_total DOUBLE DEFAULT 0;
DECLARE payment_note VARCHAR(255);
DECLARE payment_uid INTEGER;
DECLARE payment_customer INTEGER;

DECLARE giro_number VARCHAR(50);
DECLARE giro_date DATE;
DECLARE transfer_date DATE;
DECLARE bank_id INTEGER;
DECLARE bank_account_id INTEGER;
DECLARE payment_type INTEGER;
DECLARE account_id INTEGER;
DECLARE account_payable_id INTEGER;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_invoice INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_type INTEGER;
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(100) DEFAULT "";
DECLARE total DOUBLE DEFAULT 0;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4);	
DECLARE adata VARCHAR(500);


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

SET payment_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.date'));
SET payment_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.note'));
SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
SET payment_uid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.uid'));

SET giro_number = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_number'));
SET giro_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_date'));
SET transfer_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.transfer_date'));
SET bank_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_id'));
SET bank_account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_account_id'));
SET account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_id'));
SET payment_type = (SELECT M_AccountMapReffID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET journal_type = (SELECT M_AccountMapJournalType FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET account_payable_id = (SELECT M_AccountMapM_AccountID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapType = "SAL.01");

IF giro_number IS NULL THEN SET giro_number = ""; END IF;
IF bank_id IS NULL THEN SET bank_id = 0; END IF;
IF bank_account_id IS NULL THEN SET bank_account_id = 0; END IF;

IF paymentid = 0 THEN	
	SET payment_number = fn_numbering('PAY');
	SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
	INSERT INTO f_payment2(F_Payment2Date,
		F_Payment2Number,
		F_Payment2A_CustomerID,
		F_Payment2Total,
		F_Payment2Note,
		F_Payment2UserID,
		F_Payment2M_PaymentTypeID,
		F_Payment2A_BankAccountID,
		F_Payment2M_BankID,
		F_Payment2GiroDate,
		F_Payment2GiroNumber,
		F_Payment2TransferDate)
	SELECT payment_date, payment_number, payment_customer, 0, payment_note, payment_uid, payment_type, bank_account_id, bank_id, giro_date, giro_number, transfer_date;
	
	SET paymentid = (SELECT LAST_INSERT_ID());
ELSE
	UPDATE f_payment2
	SET F_Payment2Date = payment_date, F_Payment2Note = payment_note, F_Payment2A_BankAccountID = bank_account_id,
		F_Payment2M_BankID = bank_id,
		F_Payment2GiroDate = giro_date,
		F_Payment2GiroNumber = giro_number,
		F_Payment2TransferDate = transfer_date
	WHERE F_Payment2ID = paymentid;
	
	SET journal_id = (SELECT F_Payment2T_JournalID FROM f_payment2 WHERE F_Payment2ID = paymentid);
	SET payment_number = (SELECT F_Payment2Number FROM f_payment2 WHERE F_Payment2ID = paymentid);
END IF;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
	SET d_invoice = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.invoice'));
	SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.amount'));
	SET d_id = (SELECT F_Payment2DetailID FROM f_payment2detail WHERE F_Payment2DetailIsActive = "Y" AND F_Payment2DetailF_Payment2ID = paymentid AND F_Payment2DetailL_InvoiceID = d_invoice);
	
	IF d_id IS NULL THEN
		INSERT INTO f_payment2detail(F_Payment2DetailF_Payment2ID, F_Payment2DetailL_InvoiceID, F_Payment2DetailAmount)
		SELECT paymentid, d_invoice, d_amount;
		
		SET d_id = (SELECT LAST_INSERT_ID());
	ELSE
		UPDATE f_payment2detail
		SET F_Payment2DetailAmount = d_amount
		WHERE F_Payment2DetailID = d_id;
	END IF;
	
	IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
	SET n = n + 1;
	
	SET total = total + d_amount;
END WHILE;

UPDATE f_payment2detail
SET F_Payment2DetailIsActive = "N"
WHERE F_Payment2DetailF_Payment2ID = paymentid AND NOT FIND_IN_SET(F_Payment2DetailID, d_ids) AND F_Payment2DetailIsActive = "Y";

UPDATE f_payment2
SET F_Payment2Total = total WHERe F_Payment2ID = paymentid;

SET adata = JSON_ARRAY(JSON_OBJECT("account", account_id, "debit", total, "credit", 0),JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", total));
CALL sp_journal_save_notrans(journal_id, payment_date, payment_number, payment_note, adata, journal_type, account_id );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
	UPDATE f_payment2 SET F_Payment2T_JournalID = journal_id WHERE F_Payment2ID = paymentid;
END IF;

SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid) as data;

COMMIT;

END;;
DELIMITER ;
DROP PROCEDURE `sp_payment2_save_adminer_5ffb15938c402`;
DROP PROCEDURE `sp_payment2_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_payment2_save` (IN `paymentid` int, IN `hdata` text, IN `jdata` text)
BEGIN

DECLARE payment_date DATE;
DECLARE payment_number VARCHAR(25);
DECLARE payment_total DOUBLE DEFAULT 0;
DECLARE payment_note VARCHAR(255);
DECLARE payment_uid INTEGER;
DECLARE payment_customer INTEGER;

DECLARE giro_number VARCHAR(50);
DECLARE giro_date DATE;
DECLARE transfer_date DATE;
DECLARE bank_id INTEGER;
DECLARE bank_account_id INTEGER;
DECLARE payment_type INTEGER;
DECLARE account_id INTEGER;
DECLARE account_payable_id INTEGER;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_invoice INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_type INTEGER;
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(100) DEFAULT "";
DECLARE total DOUBLE DEFAULT 0;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4);	
DECLARE adata VARCHAR(500);


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

SET payment_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.date'));
SET payment_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.note'));
SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
SET payment_uid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.uid'));

SET giro_number = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_number'));
SET giro_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_date'));
SET transfer_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.transfer_date'));
SET bank_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_id'));
SET bank_account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_account_id'));
SET account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_id'));
SET payment_type = (SELECT M_AccountMapReffID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET journal_type = (SELECT M_AccountMapJournalType FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET account_payable_id = (SELECT M_AccountMapM_AccountID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapType = "SAL.01");

IF giro_number IS NULL THEN SET giro_number = ""; END IF;
IF bank_id IS NULL THEN SET bank_id = 0; END IF;
IF bank_account_id IS NULL THEN SET bank_account_id = 0; END IF;

IF paymentid = 0 THEN	
	SET payment_number = fn_numbering('PAY');
	SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
	INSERT INTO f_payment2(F_Payment2Date,
		F_Payment2Number,
		F_Payment2A_CustomerID,
		F_Payment2Total,
		F_Payment2Note,
		F_Payment2UserID,
		F_Payment2M_PaymentTypeID,
		F_Payment2A_BankAccountID,
		F_Payment2M_BankID,
		F_Payment2GiroDate,
		F_Payment2GiroNumber,
		F_Payment2TransferDate)
	SELECT payment_date, payment_number, payment_customer, 0, payment_note, payment_uid, payment_type, bank_account_id, bank_id, giro_date, giro_number, transfer_date;
	
	SET paymentid = (SELECT LAST_INSERT_ID());
ELSE
	UPDATE f_payment2
	SET F_Payment2Date = payment_date, F_Payment2Note = payment_note, F_Payment2A_BankAccountID = bank_account_id,
		F_Payment2M_BankID = bank_id,
		F_Payment2GiroDate = giro_date,
		F_Payment2GiroNumber = giro_number,
		F_Payment2TransferDate = transfer_date
	WHERE F_Payment2ID = paymentid;
	
	SET journal_id = (SELECT F_Payment2T_JournalID FROM f_payment2 WHERE F_Payment2ID = paymentid);
	SET payment_number = (SELECT F_Payment2Number FROM f_payment2 WHERE F_Payment2ID = paymentid);
END IF;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
	SET d_invoice = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.invoice'));
	SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.amount'));
	SET d_id = (SELECT F_Payment2DetailID FROM f_payment2detail WHERE F_Payment2DetailIsActive = "Y" AND F_Payment2DetailF_Payment2ID = paymentid AND F_Payment2DetailL_InvoiceID = d_invoice);
	
	IF d_id IS NULL THEN
		INSERT INTO f_payment2detail(F_Payment2DetailF_Payment2ID, F_Payment2DetailL_InvoiceID, F_Payment2DetailAmount)
		SELECT paymentid, d_invoice, d_amount;
		
		SET d_id = (SELECT LAST_INSERT_ID());
	ELSE
		UPDATE f_payment2detail
		SET F_Payment2DetailAmount = d_amount
		WHERE F_Payment2DetailID = d_id;
	END IF;
	
	IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
	SET n = n + 1;
	
	SET total = total + d_amount;
END WHILE;

UPDATE f_payment2detail
SET F_Payment2DetailIsActive = "N"
WHERE F_Payment2DetailF_Payment2ID = paymentid AND NOT FIND_IN_SET(F_Payment2DetailID, d_ids) AND F_Payment2DetailIsActive = "Y";

UPDATE f_payment2
SET F_Payment2Total = total WHERe F_Payment2ID = paymentid;

SET adata = JSON_ARRAY(JSON_OBJECT("account", account_id, "debit", total, "credit", 0),JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", total));
CALL sp_journal_save_notrans(journal_id, payment_date, payment_number, payment_note, adata, journal_type, account_id );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
	UPDATE f_payment2 SET F_Payment2T_JournalID = journal_id WHERE F_Payment2ID = paymentid;
END IF;

SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid) as data;

COMMIT;

END;;
DELIMITER ;
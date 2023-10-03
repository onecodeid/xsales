BEGIN

DECLARE payment_date DATE;
DECLARE payment_number VARCHAR(25);
DECLARE payment_total DOUBLE DEFAULT 0;
DECLARE payment_note VARCHAR(255);
DECLARE payment_uid INTEGER;

DECLARE giro_number VARCHAR(50);
DECLARE giro_date DATE;
DECLARE transfer_date DATE;
DECLARE bank_id INTEGER;
DECLARE bank_account_id INTEGER;
DECLARE account_id INTEGER;
DECLARE account_payable_id INTEGER;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_account INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_type INTEGER;
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(100) DEFAULT "";
DECLARE total DOUBLE DEFAULT 0;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4);	
DECLARE adata VARCHAR(2000);
DECLARE adata_tmp VARCHAR(2000) DEFAULT "";

DECLARE t_debit VARCHAR(25) DEFAULT "debit";
DECLARE t_credit VARCHAR(25) DEFAULT "credit";

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
SET payment_uid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.uid'));

SET giro_number = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_number'));
SET giro_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_date'));
SET transfer_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.transfer_date'));
SET bank_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_id'));
SET bank_account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_account_id'));
SET account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_id'));
SET journal_type = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.journal_type'));
SET account_payable_id = (SELECT M_AccountMapM_AccountID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapType = "SAL.01");

IF giro_number IS NULL THEN SET giro_number = ""; END IF;
IF bank_id IS NULL THEN SET bank_id = 0; END IF;
IF bank_account_id IS NULL THEN SET bank_account_id = 0; END IF;

IF paymentid = 0 THEN	
	SET payment_number = fn_numbering('PAY');
	INSERT INTO f_receive(F_ReceiveDate,
		F_ReceiveNumber,
		F_ReceiveTotal,
		F_ReceiveNote,
		F_ReceiveUserID,
		F_ReceiveA_BankAccountID,
		F_ReceiveM_BankID,
		F_ReceiveGiroDate,
		F_ReceiveGiroNumber,
		F_ReceiveTransferDate)
	SELECT payment_date, payment_number, 0, payment_note, payment_uid, bank_account_id, bank_id, giro_date, giro_number, transfer_date;
	
	SET paymentid = (SELECT LAST_INSERT_ID());
ELSE
	UPDATE f_receive
	SET F_ReceiveDate = payment_date, F_ReceiveNote = payment_note, F_ReceiveA_BankAccountID = bank_account_id,
		F_ReceiveM_BankID = bank_id,
		F_ReceiveGiroDate = giro_date,
		F_ReceiveGiroNumber = giro_number,
		F_ReceiveTransferDate = transfer_date
	WHERE F_ReceiveID = paymentid;
	
	SET journal_id = (SELECT F_ReceiveT_JournalID FROM f_receive WHERE F_ReceiveID = paymentid);
	SET payment_number = (SELECT F_ReceiveNumber FROM f_receive WHERE F_ReceiveID = paymentid);
END IF;

SET l = JSON_LENGTH(jdata);
IF t_type = "OUT" THEN
    SET t_debit = "credit";
    SET t_credit = "debit";
END IF;

WHILE n < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
	SET d_account = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.account'));
	SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.amount'));
	SET d_id = (SELECT F_ReceiveDetailID FROM f_receivedetail WHERE F_ReceiveDetailIsActive = "Y" AND F_ReceiveDetailF_ReceiveID = paymentid AND F_ReceiveDetailM_AccountID = d_account);
	
	IF d_id IS NULL THEN
		INSERT INTO f_receivedetail(F_ReceiveDetailF_ReceiveID, F_ReceiveDetailM_AccountID, F_ReceiveDetailAmount)
		SELECT paymentid, d_account, d_amount;
		
		SET d_id = (SELECT LAST_INSERT_ID());
	ELSE
		UPDATE f_receivedetail
		SET F_ReceiveDetailAmount = d_amount
		WHERE F_ReceiveDetailID = d_id;
	END IF;
	
	IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
	SET n = n + 1;

    IF adata_tmp = "" THEN
        SET adata_tmp = JSON_OBJECT("account", d_account, t_debit, 0, t_credit, d_amount);
    ELSE
        SET adata_tmp = CONCAT(adata_tmp, ",", JSON_OBJECT("account", d_account, t_debit, 0, t_credit, d_amount));
    END IF;
	
	SET total = total + d_amount;
END WHILE;

UPDATE f_receivedetail
SET F_ReceiveDetailIsActive = "N"
WHERE F_ReceiveDetailF_ReceiveID = paymentid AND NOT FIND_IN_SET(F_ReceiveDetailID, d_ids) AND F_ReceiveDetailIsActive = "Y";

UPDATE f_receive
SET F_ReceiveTotal = total WHERe F_ReceiveID = paymentid;

SET adata = CONCAT("[", JSON_OBJECT("account", account_id, t_debit, total, t_credit, 0), ",", adata_tmp, "]");
CALL sp_journal_save_notrans(journal_id, payment_date, payment_number, payment_note, adata, journal_type, account_id );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
	UPDATE f_receive SET F_ReceiveT_JournalID = journal_id WHERE F_ReceiveID = paymentid;
END IF;

SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid) as data;

COMMIT;

END
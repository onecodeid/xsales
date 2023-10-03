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
DECLARE account_disc_id INTEGER;
DECLARE customer_name VARCHAR(100);

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_invoice INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_type INTEGER;
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(1000) DEFAULT "";
DECLARE d_disc CHAR(1);
DECLARE total DOUBLE DEFAULT 0;
DECLARE total_disc DOUBLE DEFAULT 0;
DECLARE o_amount DOUBLE;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4);	
DECLARE adata VARCHAR(500);
DECLARE journal_disc_id INTEGER DEFAULT 0;


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
SET customer_name = (SELECT a_customername FROM one_iv.a_customer WHERE A_CustomerID = payment_customer);

SET giro_number = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_number'));
SET giro_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_date'));
SET transfer_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.transfer_date'));
SET bank_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_id'));
SET bank_account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_account_id'));
SET account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_id'));
SET payment_type = (SELECT M_AccountMapReffID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET journal_type = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.journal_type'));

SET account_payable_id = (SELECT M_AccountMapM_AccountID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapType = "SAL.01");
SET account_disc_id = (SELECT M_AccountID FROM m_account WHERE M_AccountIsActive = "Y" AND M_AccountCode = "4-40100");

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
    SET d_disc = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.disc'));
        IF d_disc IS NULL THEN SET d_disc = "N"; ENd IF;

	SET d_id = (SELECT F_Payment2DetailID FROM f_payment2detail WHERE F_Payment2DetailIsActive = "Y" AND F_Payment2DetailF_Payment2ID = paymentid AND F_Payment2DetailL_InvoiceID = d_invoice AND F_Payment2DetailIsDisc = d_disc);
	
	IF d_id IS NULL THEN
		INSERT INTO f_payment2detail(F_Payment2DetailF_Payment2ID, F_Payment2DetailL_InvoiceID, F_Payment2DetailAmount, F_Payment2DetailIsDisc)
		SELECT paymentid, d_invoice, d_amount, d_disc;
		
        
        UPDATE one_iv.l_invoice 
        SET L_InvoicePaid = L_InvoicePaid + d_amount
        WHERE L_InvoiceID = d_invoice;

		SET d_id = (SELECT LAST_INSERT_ID());
	ELSE
        SET o_amount = (SELECT F_Payment2DetailAmount FROM f_payment2detail WHERE F_Payment2DetailID = d_id);

		UPDATE f_payment2detail
		SET F_Payment2DetailAmount = d_amount, F_Payment2DetailIsDisc = d_disc
		WHERE F_Payment2DetailID = d_id;

        
        UPDATE one_iv.l_invoice 
        SET L_InvoicePaid = L_InvoicePaid + d_amount - o_amount
        WHERE L_InvoiceID = d_invoice;
	END IF;
	
	IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
	SET n = n + 1;
	
    IF d_disc = "N" THEN
	    SET total = total + d_amount;
    ELSE
        SET total_disc = total_disc + d_amount;
    END IF;
END WHILE;


UPDATE one_iv.l_invoice
JOIN f_payment2detail ON F_Payment2DetailL_InvoiceID = L_InvoiceID AND F_Payment2DetailIsActive = "Y"
    AND F_Payment2DetailF_Payment2ID = paymentid
    AND NOT FIND_IN_SET(F_Payment2DetailID, d_ids)
SET L_InvoicePaid = L_InvoicePaid - F_Payment2DetailAmount;

UPDATE f_payment2detail
SET F_Payment2DetailIsActive = "N"
WHERE F_Payment2DetailF_Payment2ID = paymentid AND NOT FIND_IN_SET(F_Payment2DetailID, d_ids) AND F_Payment2DetailIsActive = "Y";


UPDATE f_payment2
SET F_Payment2Total = total + total_disc WHERe F_Payment2ID = paymentid;

SET adata = JSON_ARRAY(JSON_OBJECT("account", account_id, "debit", total, "credit", 0),JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", total));
CALL sp_journal_save_notrans(journal_id, payment_date, payment_number, payment_note, adata, journal_type, account_id );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
	UPDATE f_payment2 SET F_Payment2T_JournalID = journal_id WHERE F_Payment2ID = paymentid;
END IF;

UPDATE t_journal SET T_JournalRefNote = customer_name WHERE T_JournalID = journal_id;
SET journal_disc_id = (SELECT F_Payment2DiscT_JournalID FROM f_payment2 WHERE F_Payment2ID = paymentid);

-- IF JOURNAL DISC NOT FOUND
IF journal_disc_id = journal_id OR journal_disc_id IS NULL THEN SET journal_disc_id = 0; END IF;
IF total_disc <> 0 THEN
    SET adata = JSON_ARRAY(JSON_OBJECT("account", account_disc_id, "debit", total_disc, "credit", 0),JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", total_disc));

    CALL sp_journal_save_notrans(journal_disc_id, payment_date, payment_number, payment_note, adata, journal_type, account_disc_id );
    IF journal_disc_id = 0 THEN
        SET journal_disc_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
        UPDATE f_payment2 SET F_Payment2DiscT_JournalID = journal_disc_id WHERE F_Payment2ID = paymentid;
    END IF;
ELSEIF journal_disc_id <> 0 THEN
    CALL sp_journal_delete_notrans(journal_disc_id);
    UPDATE f_payment2 SET F_Payment2DiscT_JournalID = 0 WHERE F_Payment2ID = paymentid;
END IF;

SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid) as data;

COMMIT;

END
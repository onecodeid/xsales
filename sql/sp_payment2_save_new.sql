BEGIN

DECLARE payment_date DATE;
DECLARE payment_number VARCHAR(25);
DECLARE payment_total DOUBLE DEFAULT 0;
DECLARE payment_note VARCHAR(2000);
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
DECLARE account_revenue_id INTEGER;
DECLARE account_retur_id INTEGER;
DECLARE account_tmp_id INTEGER;
DECLARE customer_name VARCHAR(100);
DECLARE staff_name VARCHAR(100) DEFAULT "";
DECLARE account_name VARCHAR(100);
DECLARE account_payable_name VARCHAR(100);

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_invoice INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_type INTEGER;
DECLARE d_id INTEGER;
DECLARE d_id_2 INTEGER;
DECLARE d_ids VARCHAR(1000) DEFAULT "";
DECLARE d_disc CHAR(1);
DECLARE d_is_retur CHAR(1);
DECLARE d_retur_id INTEGER;
DECLARE total DOUBLE DEFAULT 0;
DECLARE total_disc DOUBLE DEFAULT 0;
DECLARE total_change DOUBLE DEFAULT 0;
DECLARE total_change_disc DOUBLE DEFAULT 0;
DECLARE total_change_retur DOUBLE DEFAULT 0;
DECLARE total_retur DOUBLE DEFAULT 0;
DECLARE o_amount DOUBLE;
DECLARE o_change DOUBLE;
DECLARE d_unpaid DOUBLE;
DECLARE d_change DOUBLE DEFAULT 0;
DECLARE d_change_disc DOUBLE DEFAULT 0;
DECLARE d_change_retur DOUBLE DEFAULT 0;
DECLARE d_x_change DOUBLE DEFAULT 0;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4);	
DECLARE adata VARCHAR(2500);
DECLARE journal_disc_id INTEGER DEFAULT 0;
DECLARE jcommit CHAR(1) DEFAULT "N";

DECLARE tags VARCHAR(255);

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME, @clm = COLUMN_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name, @clm as column_name;

ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME, @clm = COLUMN_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name, @clm as column_name;

ROLLBACK;
END;

START TRANSACTION;

SET payment_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.date'));
SET payment_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.note'));
SET payment_customer = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
SET payment_uid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.uid'));
SET customer_name = (SELECT m_customername FROM m_customer WHERE M_CustomerID = payment_customer);

SET giro_number = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_number'));
SET giro_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_date'));
SET transfer_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.transfer_date'));
SET bank_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_id'));
SET bank_account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_account_id'));
SET account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_id'));
SET payment_type = (SELECT M_AccountMapReffID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET journal_type = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.journal_type'));

SET account_payable_id = (SELECT fn_master_get_account_id("ACC.PAYABLE"));
SET account_disc_id = (SELECT fn_master_get_account_id("ACC.SALES.DISC"));
SET account_revenue_id = (SELECT fn_master_get_account_id("ACC.INCOME.OTHER"));
SET account_retur_id = (SELECT fn_master_get_account_id("ACC.SALES.RETUR"));

SET tags = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.tags'));

IF giro_number IS NULL THEN SET giro_number = ""; END IF;
IF bank_id IS NULL THEN SET bank_id = 0; END IF;
IF bank_account_id IS NULL THEN SET bank_account_id = 0; END IF;
IF tags IS NULL THEN SET tags = "[]"; END IF;

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

	-- UPDATE TO UNSTATE
    UPDATE f_payment2detail
    SET F_Payment2DetailIsActive = "O"
    WHERE F_Payment2DetailIsActive = "Y" AND F_Payment2DetailF_Payment2ID = paymentid;
	
	-- UPDATE INVOICE
    UPDATE l_invoice
    JOIN (
        SELECT F_Payment2DetailL_InvoiceID invoice_id, SUM(F_Payment2DetailAmount) amnt
        FROM f_payment2detail
        WHERE F_Payment2DetailIsActive = "O" AND F_Payment2DetailF_Payment2ID = paymentid
        GROUP BY F_Payment2DetailL_InvoiceID
    ) x on invoice_id = L_InvoiceID
    SET L_InvoicePaid = L_InvoicePaid - amnt;

	-- UPDATE RETUR
    -- UPDATE l_retur
    -- JOIN (
    --     SELECT F_Payment2DetailL_ReturID retur_id, SUM(F_Payment2DetailAmount) amnt
    --     FROM f_payment2detail
    --     WHERE F_Payment2DetailIsActive = "O" AND F_Payment2DetailF_Payment2ID = paymentid AND F_Payment2DetailIsRetur = "Y" AND F_Payment2DetailL_ReturID <> 0
    --     GROUP BY F_Payment2DetailL_ReturID
    -- ) x on retur_id = L_ReturID
    -- SET L_ReturUsed = L_ReturUsed - amnt;

	SET journal_id = (SELECT F_Payment2T_JournalID FROM f_payment2 WHERE F_Payment2ID = paymentid);
	SET payment_number = (SELECT F_Payment2Number FROM f_payment2 WHERE F_Payment2ID = paymentid);
END IF;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
    SET d_change = 0;
	SET d_change_disc = 0;
	SET d_change_retur = 0;
	SET d_x_change = 0;
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
	SET d_invoice = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.invoice'));
	SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.amount'));
    SET d_disc = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.disc'));
	SET d_is_retur = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.is_retur'));
	SET d_retur_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.retur'));
	SET o_amount = 0;

		IF d_is_retur IS NULL THEN SET d_is_retur = "N"; END IF;
		IF d_retur_id IS NULL THEN SET d_retur_id = 0; END IF;
        IF d_disc IS NULL THEN SET d_disc = "N"; ENd IF;

	SET d_id = (SELECT F_Payment2DetailID FROM f_payment2detail WHERE F_Payment2DetailIsActive = "O" AND F_Payment2DetailF_Payment2ID = paymentid AND F_Payment2DetailL_InvoiceID = d_invoice AND F_Payment2DetailIsDisc = d_disc AND F_Payment2DetailIsRetur = d_is_retur AND F_Payment2DetailL_ReturID = d_retur_id);
	
	IF d_id IS NULL THEN
        SET d_unpaid = (SELECT L_InvoiceUnpaid FROM l_invoice WHERE L_InvoiceID = d_invoice);
        IF d_amount > d_unpaid THEN
			IF d_is_retur = "Y" THEN
				SET d_change_retur = d_change_retur + d_amount - d_unpaid;
			ELSEIF d_disc = "Y" THEN
				SET d_change_disc = d_change_disc + d_amount - d_unpaid;
			ELSE
            	SET d_change = d_change + d_amount - d_unpaid;
			END IF;
			SET d_x_change = d_amount - d_unpaid;
            SET d_amount = d_unpaid;
        END IF;

		INSERT INTO f_payment2detail(F_Payment2DetailF_Payment2ID, F_Payment2DetailL_InvoiceID, F_Payment2DetailAmount, F_Payment2DetailChange, F_Payment2DetailIsDisc, F_Payment2DetailIsRetur, F_Payment2DetailL_ReturID)
		SELECT paymentid, d_invoice, d_amount + d_x_change, d_x_change, d_disc, d_is_retur, d_retur_id;

		SET d_id = (SELECT LAST_INSERT_ID());
    
    -- UPDATE OLD RECORD
    -- -
	ELSE

        SET o_amount = 0;
		SET o_change = 0;
        SET d_unpaid = (SELECT L_InvoiceUnpaid FROM l_invoice WHERE L_InvoiceID = d_invoice);
        SET d_unpaid = d_unpaid + o_amount - o_change;

        IF d_amount > d_unpaid THEN
            IF d_is_retur = "Y" THEN
				SET d_change_retur = d_change_retur + d_amount - d_unpaid;
			ELSEIF d_disc = "Y" THEN
				SET d_change_disc = d_change_disc + d_amount - d_unpaid;
			ELSE
            	SET d_change = d_change + d_amount - d_unpaid;
			END IF;
			SET d_x_change = d_amount - d_unpaid;
            SET d_amount = d_unpaid;
        END IF;

		UPDATE f_payment2detail
		SET F_Payment2DetailAmount = d_amount + d_x_change, F_Payment2DetailChange = d_x_change, F_Payment2DetailIsDisc = d_disc, F_Payment2DetailIsRetur = d_is_retur, F_Payment2DetailL_ReturID = d_retur_id, F_Payment2DetailIsActive = "Y"
		WHERE F_Payment2DetailID = d_id;
	END IF;
	
	-- UPDATE INVOICe
    UPDATE l_invoice 
    SET L_InvoicePaid = L_InvoicePaid + d_amount - o_amount + d_x_change
    WHERE L_InvoiceID = d_invoice;

    -- UPDATE RETUR
    -- IF d_retur_id <> 0 THEN
    --     UPDATE l_retur SET L_ReturUsed = L_ReturUsed + d_amount - o_amount WHERE L_ReturID = d_retur_id;
    -- END IF;

	SET n = n + 1;
	
    IF d_disc = "N" AND d_is_retur = "N" THEN
	    SET total = total + d_amount;
    ELSEIF d_disc = "Y" AND d_is_retur ="N" THEN
        SET total_disc = total_disc + d_amount;
	ELSE
		SET total_retur = total_retur + d_amount;
    END IF;

    SET total_change = total_change + d_change;
	SET total_change_disc = total_change_disc + d_change_disc;
	SET total_change_retur = total_change_retur + d_change_retur;
END WHILE;

SET d_ids = (SELECT CONCAT('[', GROUP_CONCAT(F_Payment2DetailID), ']')
            FROM f_payment2detail
            WHERE F_Payment2DetailIsActive = "O" AND F_Payment2DetailF_Payment2ID = paymentid);
IF d_ids IS null THEN SET d_ids = "[]"; END IF;

SET n = 0;
SET l = JSON_LENGTH(d_ids);
WHILE n < l DO
    SET d_id = JSON_UNQUOTE(JSON_EXTRACT(d_ids, CONCAT("$[", n, "]")));

SELECT F_Payment2DetailL_InvoiceID, F_Payment2DetailIsRetur, F_Payment2DetailIsDisc,F_Payment2DetailL_ReturID, F_Payment2DetailAmount,
    F_Payment2DetailChange
    INTO d_invoice, d_is_retur, d_disc, d_retur_id, d_amount, d_change
    FROM f_payment2detail
    WHERE F_Payment2DetailID = d_id;

	UPDATE f_payment2detail
    SET F_Payment2DetailIsActive = "N"
    WHERE F_Payment2DetailID = d_id;

	SET n = n + 1;
END WHILE;

UPDATE f_payment2
SET F_Payment2Total = total + total_disc + total_change + total_retur WHERe F_Payment2ID = paymentid;

SET account_tmp_id = (SELECT fn_master_get_account_id('ACC.GIRO'));
IF account_id = account_tmp_id THEN
	SET account_id = (SELECT fn_master_get_account_id('ACC.PAYABLE.GIRO'));
END IF;

SET account_name = (SELECT M_AccountName FROM m_account WHERE M_AccountID = account_id);
SET account_payable_name = (SELECT M_AccountName FROM m_account WHERE M_AccountID = account_payable_id);

SET adata = CONCAT(JSON_OBJECT("account", account_id, "debit", total+total_change, "credit", 0, "ledger_note", JSON_ARRAY(account_payable_name, customer_name)),
			",", JSON_OBJECT("account", account_payable_id, "debit", 0, "credit", total+total_disc+total_retur,
                "ledger_note", JSON_ARRAY(account_name, customer_name)));

IF total_disc <> 0 THEN
	SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_disc_id, "debit", total_disc+total_change_disc, "credit", 0, "ledger_note", JSON_ARRAY(account_payable_name, customer_name)));
END IF;

IF total_retur <> 0 THEN
	SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_retur_id, "debit", total_retur+total_change_retur, "credit", 0, "ledger_note", JSON_ARRAY(account_payable_name, customer_name)));
END IF;

IF total_change <> 0 OR total_change_disc <> 0 OR total_change_retur <> 0 THEN
	SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_revenue_id, "debit", 0, "credit", total_change+total_change_retur+total_change_disc, "ledger_note", JSON_ARRAY(account_name, customer_name)));
END IF;

SET adata = CONCAT("[", adata, "]");
SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid) as data;

CALL sp_journal_save_tags_notrans(journal_id, payment_date, payment_number, payment_note, adata, journal_type, account_id, tags );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
	UPDATE f_payment2 SET F_Payment2T_JournalID = journal_id WHERE F_Payment2ID = paymentid;
END IF;

UPDATE t_journal SET T_JournalUserID = payment_uid, T_JournalRefNote = customer_name, T_JournalRefID = payment_customer WHERE T_JournalID = journal_id;
SET journal_disc_id = (SELECT F_Payment2DiscT_JournalID FROM f_payment2 WHERE F_Payment2ID = paymentid);

-- GIRO
IF payment_type = 3 THEN
	CALL sp_giro_save('J.11', paymentid, payment_uid);
END IF;

SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid) as data;

COMMIT;

END
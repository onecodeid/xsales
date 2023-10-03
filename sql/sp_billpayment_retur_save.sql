BEGIN

DECLARE payment_date DATE;
DECLARE payment_number VARCHAR(25);
DECLARE payment_total DOUBLE DEFAULT 0;
DECLARE payment_note VARCHAR(255);
DECLARE payment_uid INTEGER;
DECLARE payment_supplier INTEGER;

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
DECLARE supplier_name VARCHAR(100);

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_bill INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_type INTEGER;
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(100) DEFAULT "";
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
DECLARE d_unpaid DOUBLE;
DECLARE d_change DOUBLE DEFAULT 0;
DECLARE d_change_disc DOUBLE DEFAULT 0;
DECLARE d_change_retur DOUBLE DEFAULT 0;
DECLARE d_x_change DOUBLE DEFAULT 0;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4);	
DECLARE adata VARCHAR(500);
DECLARE jcommit CHAR(1) DEFAULT "N";


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
SET payment_supplier = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.supplier'));
SET payment_uid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.uid'));
SET supplier_name = (SELECT a_suppliername FROM one_iv.a_supplier WHERE A_SupplierID = payment_supplier);

SET giro_number = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_number'));
SET giro_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.giro_date'));
SET transfer_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.transfer_date'));
SET bank_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_id'));
SET bank_account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.bank_account_id'));
SET account_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_id'));
SET payment_type = (SELECT M_AccountMapReffID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapM_AccountID = account_id AND M_AccountMapType LIKE "PAY.%");
SET journal_type = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.journal_type'));


SET account_payable_id = (SELECT M_AccountID FROM m_account WHERE M_AccountCode = "2-20100" AND M_AccountIsActive = "Y" LIMIT 1);
SET account_disc_id = (SELECT M_AccountID FROM m_account WHERE M_AccountIsActive = "Y" AND M_AccountCode = "5-50100");
SET account_revenue_id = (SELECT M_AccountID FROM m_account WHERE M_AccountIsActive = "Y" AND M_AccountCode = "8-80999");
SET account_retur_id = (SELECT M_AccountID FROM m_account WHERE M_AccountIsActive = "Y" AND M_AccountCode = "5-50200");

IF giro_number IS NULL THEN SET giro_number = ""; END IF;
IF bank_id IS NULL THEN SET bank_id = 0; END IF;
IF bank_account_id IS NULL THEN SET bank_account_id = 0; END IF;

IF paymentid = 0 THEN	
	SET payment_number = fn_numbering('PAY');
	SET payment_supplier = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.supplier'));
	INSERT INTO f_billpayment(F_BillPaymentDate,
		F_BillPaymentNumber,
		F_BillPaymentA_SupplierID,
		F_BillPaymentTotal,
		F_BillPaymentNote,
		F_BillPaymentUserID,
		F_BillPaymentM_PaymentTypeID,
		F_BillPaymentA_BankAccountID,
		F_BillPaymentM_BankID,
		F_BillPaymentGiroDate,
		F_BillPaymentGiroNumber,
		F_BillPaymentTransferDate)
	SELECT payment_date, payment_number, payment_supplier, 0, payment_note, payment_uid, payment_type, bank_account_id, bank_id, giro_date, giro_number, transfer_date;
	
	SET paymentid = (SELECT LAST_INSERT_ID());
ELSE
	SET journal_id = (SELECT F_BillPaymentT_JournalID FROM f_billpayment WHERE F_BillPaymentID = paymentid);
    SET jcommit = (SELECT T_JournalCommit FROM t_journal WHERE T_JournalID = journal_id);
	SET payment_number = (SELECT F_BillPaymentNumber FROM f_billpayment WHERE F_BillPaymentID = paymentid);

	IF jcommit = "Y" THEN
        SELECT "ERR" status, "Jurnal tersebut telah di-COMMIT :(" message;
        ROLLBACK;
    ELSE
		UPDATE f_billpayment
		SET F_BillPaymentDate = payment_date, F_BillPaymentNote = payment_note, F_BillPaymentA_BankAccountID = bank_account_id,
			F_BillPaymentM_BankID = bank_id,
			F_BillPaymentGiroDate = giro_date,
			F_BillPaymentGiroNumber = giro_number,
			F_BillPaymentTransferDate = transfer_date
		WHERE F_BillPaymentID = paymentid;
	END IF;
END IF;

IF jcommit = "N" THEN
	
	
	UPDATE f_billpaymentdetail
	SET F_BillPaymentDetailIsActive = "O"
	WHERE F_BillPaymentDetailF_BillPaymentID = paymentid
	AND F_BillPaymentDetailIsActive = "Y";

	
	UPDATE one_iv.p_purchiveorder
	JOIN (
		SELECT F_BillPaymentDetailP_PurchiveOrderID bill_id, SUM(F_BillPaymentDetailAmount) amnt
		FROM f_billpaymentdetail
		WHERE F_BillPaymentDetailIsActive = "O" AND F_BillPaymentDetailF_BillPaymentID = paymentid
		GROUP BY F_BillPaymentDetailP_PurchiveOrderID
	) x on bill_id = P_PurchiveOrderID
	SET P_PurchiveOrderPaid = P_PurchiveOrderPaid - amnt;

	
	UPDATE p_retur
	JOIN (
		SELECT F_BillPaymentDetailP_ReturID retur_id, SUM(F_BillPaymentDetailAmount) amnt
		FROM f_billpaymentdetail
		WHERE F_BillPaymentDetailIsActive = "O" AND F_BillPaymentDetailF_BillPaymentID = paymentid AND F_BillPaymentDetailIsRetur = "Y" AND F_BillPaymentDetailP_ReturID <> 0
		GROUP BY F_BillPaymentDetailP_ReturID
	) x on retur_id = P_ReturID
	SET P_ReturUsed = P_ReturUsed - amnt;

	SET l = JSON_LENGTH(jdata);
	WHILE n < l DO
		SET d_change = 0;
		SET d_change_disc = 0;
		SET d_change_retur = 0;
		SET d_x_change = 0;
		SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
		SET d_bill = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.bill'));
		SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.amount'));
		SET o_amount = 0;
		SET d_disc = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.disc'));
		SET d_is_retur = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.is_retur'));
		SET d_retur_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.retur'));

			IF d_is_retur IS NULL THEN SET d_is_retur = "N"; END IF;
			IF d_retur_id IS NULL THEN SET d_retur_id = 0; END IF;
			IF d_disc IS NULL THEN SET d_disc = "N"; ENd IF;

		SET d_id = (SELECT F_BillPaymentDetailID FROM f_billpaymentdetail WHERE F_BillPaymentDetailIsActive = "Y" AND F_BillPaymentDetailF_BillPaymentID = paymentid AND F_BillPaymentDetailP_PurchiveOrderID = d_bill AND F_BillPaymentDetailIsDisc = d_disc AND F_BillPaymentDetailIsRetur = d_is_retur AND F_BillPaymentDetailP_ReturID = d_retur_id);
		
		IF d_id IS NULL THEN
			SET d_unpaid = (SELECT P_PurchiveOrderUnpaid FROM one_iv.p_purchiveorder WHERE P_PurchiveOrderID = d_bill);
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

			INSERT INTO f_billpaymentdetail(F_BillPaymentDetailF_BillPaymentID, F_BillPaymentDetailP_PurchiveOrderID, F_BillPaymentDetailAmount, F_BillPaymentDetailChange, F_BillPaymentDetailIsDisc, F_BillPaymentDetailIsRetur, F_BillPaymentDetailP_ReturID)
			SELECT paymentid, d_bill, d_amount + d_x_change, d_x_change, d_disc, d_is_retur, d_retur_id;

			SET d_id = (SELECT LAST_INSERT_ID());
		ELSE
			SET o_amount = 0;
			
			SET d_unpaid = (SELECT P_PurchiveOrderUnpaid FROM one_iv.p_purchiveorder WHERE P_PurchiveOrderID = d_bill);
			SET d_unpaid = d_unpaid + o_amount;

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

			UPDATE f_billpaymentdetail
			SET F_BillPaymentDetailAmount = d_amount + d_x_change, F_BillPaymentDetailChange = d_x_change, F_BillPaymentDetailIsDisc = d_disc, F_BillPaymentDetailIsRetur = d_is_retur, F_BillPaymentDetailP_ReturID = d_retur_id,
			F_BillPaymentDetailIsActive = "Y"
			WHERE F_BillPaymentDetailID = d_id;
		END IF;
		
		
		UPDATE one_iv.p_purchiveorder 
		SET P_PurchiveOrderPaid = P_PurchiveOrderPaid + d_amount - o_amount + d_x_change
		WHERE P_PurchiveOrderID = d_bill;

		
		IF d_retur_id <> 0 THEN
			UPDATE p_retur SET P_ReturUsed = P_ReturUsed + d_amount - o_amount WHERE P_ReturID = d_retur_id;
		END IF;

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

	SET d_ids = (SELECT CONCAT('[', GROUP_CONCAT(F_BillPaymentDetailID), ']')
				FROM f_billpaymentdetail
				WHERE F_BillPaymentDetailIsActive = "O" AND F_BillPaymentDetailF_BillPaymentID = paymentid);
	IF d_ids IS null THEN SET d_ids = "[]"; END IF;

	SET n = 0;
	SET l = JSON_LENGTH(d_ids);
	WHILE n < l DO
		SET d_id = JSON_UNQUOTE(JSON_EXTRACT(d_ids, CONCAT("$[", n, "]")));
		
		SELECT F_BillPaymentDetailP_PurchiveOrderID, F_BillPaymentDetailIsRetur, F_BillPaymentDetailIsDisc,F_BillPaymentDetailP_ReturID, F_BillPaymentDetailAmount,
		F_BillPaymentDetailChange
		INTO d_bill, d_is_retur, d_disc, d_retur_id, d_amount, d_change
		FROM f_billpaymentdetail
		WHERE F_BillPaymentDetailID = d_id;

		UPDATE f_billpaymentdetail
		SET F_BillPaymentDetailIsActive = "N"
		WHERE F_BillPaymentDetailID = d_id;

		SET n = n + 1;
	END WHILE;

	UPDATE f_billpayment
	SET F_BillPaymentTotal = total WHERe F_BillPaymentID = paymentid;

	SET account_tmp_id = (SELECT fn_master_get_account_id('ACC.GIRO'));
    IF account_id = account_tmp_id THEN
        SET account_id = (SELECT fn_master_get_account_id('ACC.DEBT.GIRO'));
    END IF;
	SET adata = CONCAT(JSON_OBJECT("account", account_id, "credit", total+total_change, "debit", 0),
				",", JSON_OBJECT("account", account_payable_id, "credit", 0, "debit", total+total_disc+total_retur));

	IF total_disc <> 0 THEN
		SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_disc_id, "credit", total_disc+total_change_disc, "debit", 0));
	END IF;

	IF total_retur <> 0 THEN
		SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_retur_id, "credit", total_retur+total_change_retur, "debit", 0));
	END IF;

	IF total_change <> 0 OR total_change_disc <> 0 OR total_change_retur <> 0 THEN
		SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_revenue_id, "credit", 0, "debit", total_change+total_change_retur+total_change_disc));
	END IF;

	SET adata = CONCAT("[", adata, "]");


	CALL sp_journal_save_notrans(journal_id, payment_date, payment_number, payment_note, adata, journal_type, account_id );

	IF journal_id = 0 THEN
		SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
		UPDATE f_billpayment SET F_BillPaymentT_JournalID = journal_id WHERE F_BillPaymentID = paymentid;
	END IF;

	UPDATE t_journal SET T_JournalUserID = payment_uid, T_JournalRefNote = supplier_name, T_JournalRefID = payment_supplier WHERE T_JournalID = journal_id;

	
	IF payment_type = 3 THEN
		CALL sp_giro_save('J.12', paymentid, payment_uid);
	END IF;

	SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid, "total_change_disc", total_change_disc) as data;

	COMMIT;

END IF;

END
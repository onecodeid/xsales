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
DECLARE account_advance_id INTEGER;
DECLARE supplier_name VARCHAR(100);

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_bill INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_type INTEGER;
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(100) DEFAULT "[]";
DECLARE d_ids_retur VARCHAR(100) DEFAULT "[]";
DECLARE d_ids_dp VARCHAR(100) DEFAULT "[]";
DECLARE d_disc CHAR(1);
DECLARE d_is_retur CHAR(1);
DECLARE d_retur_id INTEGER;
DECLARE total DOUBLE DEFAULT 0;
DECLARE total_disc DOUBLE DEFAULT 0;
DECLARE total_change DOUBLE DEFAULT 0;
DECLARE total_change_disc DOUBLE DEFAULT 0;
DECLARE total_change_retur DOUBLE DEFAULT 0;
DECLARE total_change_dp DOUBLE DEFAULT 0;
DECLARE total_retur DOUBLE DEFAULT 0;
DECLARE total_dp DOUBLE DEFAULT 0;
DECLARE o_amount DOUBLE;
DECLARE d_unpaid DOUBLE;
DECLARE o_change DOUBLE;
DECLARE d_change DOUBLE DEFAULT 0;
DECLARE d_change_disc DOUBLE DEFAULT 0;
DECLARE d_change_retur DOUBLE DEFAULT 0;
DECLARE d_change_dp DOUBLE DEFAULT 0;
DECLARE d_x_change DOUBLE DEFAULT 0;
DECLARE d_type_id INTEGER;
DECLARE d_type_code VARCHAR(25);
DECLARE d_dp_id INTEGER;




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
SET payment_supplier = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.supplier'));
SET payment_uid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.uid'));
SET supplier_name = (SELECT M_VendorName FROM m_vendor WHERE M_VendorID = payment_supplier);

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
SET account_advance_id = (SELECT M_AccountID FROM m_account WHERE M_AccountIsActive = "Y" AND M_AccountCode = "1-10403");


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
	UPDATE f_billpayment
	SET F_BillPaymentDate = payment_date, F_BillPaymentNote = payment_note, F_BillPaymentA_BankAccountID = bank_account_id,
		F_BillPaymentM_BankID = bank_id,
		F_BillPaymentGiroDate = giro_date,
		F_BillPaymentGiroNumber = giro_number,
		F_BillPaymentTransferDate = transfer_date
	WHERE F_BillPaymentID = paymentid;
	
	SET journal_id = (SELECT F_BillPaymentT_JournalID FROM f_billpayment WHERE F_BillPaymentID = paymentid);
	SET payment_number = (SELECT F_BillPaymentNumber FROM f_billpayment WHERE F_BillPaymentID = paymentid);
END IF;


UPDATE f_billpaymentdetail
SET F_BillPaymentDetailIsActive = "O"
WHERE F_BillPaymentDetailIsActive = "Y"
AND F_BillPaymentDetailF_BillPaymentID = paymentid;





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
	SET d_dp_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.dp'));

	SET d_type_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.type'));
	SET d_type_code = (SELECT M_PaymentDetailCode FROM m_paymentdetail WHERE M_PaymentDetailID = d_type_id);

		IF d_is_retur IS NULL THEN SET d_is_retur = "N"; END IF;
		IF d_retur_id IS NULL THEN SET d_retur_id = 0; END IF;
        IF d_disc IS NULL THEN SET d_disc = "N"; ENd IF;
		IF d_dp_id IS NULL THEN SET d_dp_id = 0; END IF;

	SET d_id = (SELECT F_BillPaymentDetailID 
		FROM f_billpaymentdetail 
		WHERE F_BillPaymentDetailIsActive = "O" 
		AND F_BillPaymentDetailF_BillPaymentID = paymentid 
		AND F_BillPaymentDetailF_BillID = d_bill 
		AND F_BillPaymentDetailIsDisc = d_disc 
		AND F_BillPaymentDetailIsRetur = d_is_retur 
		AND F_BillPaymentDetailP_PurchaseReturID = d_retur_id
		AND F_BillPaymentDetailM_PaymentDetailID = d_type_id);
	
	IF d_id IS NULL THEN
		SET d_unpaid = (SELECT F_BillUnpaid FROM f_bill WHERE F_BillID = d_bill);
		IF d_amount > d_unpaid THEN
			IF d_is_retur = "Y" THEN
				SET d_change_retur = d_change_retur + d_amount - d_unpaid;
			ELSEIF d_disc = "Y" THEN
				SET d_change_disc = d_change_disc + d_amount - d_unpaid;
			ELSEIF d_type_code = "PAY.DP" THEN
				SET d_change_dp = d_change_dp + d_amount - d_unpaid;
			ELSE
            	SET d_change = d_change + d_amount - d_unpaid;
			END IF;
			SET d_x_change = d_amount - d_unpaid;
            SET d_amount = d_unpaid;
        END IF;

		INSERT INTO f_billpaymentdetail(F_BillPaymentDetailF_BillPaymentID, F_BillPaymentDetailF_BillID, F_BillPaymentDetailAmount, F_BillPaymentDetailChange, F_BillPaymentDetailIsDisc, F_BillPaymentDetailIsRetur, F_BillPaymentDetailP_PurchaseReturID, F_BillPaymentDetailF_BillDpID, F_BillPaymentDetailM_PaymentDetailID)
		SELECT paymentid, d_bill, d_amount + d_x_change, d_x_change, d_disc, d_is_retur, d_retur_id, d_dp_id, d_type_id;
		
		
        
        

		SET d_id = (SELECT LAST_INSERT_ID());
	ELSE
		SET o_amount = (SELECT F_BillPaymentDetailAmount FROM f_billpaymentdetail WHERE F_BillPaymentDetailID = d_id);
		SET o_change = (SELECT F_BillPaymentDetailChange FROM f_billpaymentdetail WHERE F_BillPaymentDetailID = d_id);
		SET d_unpaid = (SELECT F_BillUnpaid FROM f_bill WHERE F_BillID = d_bill);
        SET d_unpaid = d_unpaid + o_amount - o_change;

		IF d_amount > d_unpaid THEN
            IF d_is_retur = "Y" THEN
				SET d_change_retur = d_change_retur + d_amount - d_unpaid;
			ELSEIF d_disc = "Y" THEN
				SET d_change_disc = d_change_disc + d_amount - d_unpaid;
			ELSEIF d_type_code = "PAY.DP" THEN
				SET d_change_dp = d_change_dp + d_amount - d_unpaid;
			ELSE
            	SET d_change = d_change + d_amount - d_unpaid;
			END IF;
			SET d_x_change = d_amount - d_unpaid;
            SET d_amount = d_unpaid;
        END IF;

		UPDATE f_billpaymentdetail
		SET F_BillPaymentDetailAmount = d_amount + d_x_change, F_BillPaymentDetailChange = d_x_change, F_BillPaymentDetailIsDisc = d_disc, F_BillPaymentDetailIsRetur = d_is_retur, F_BillPaymentDetailP_PurchaseReturID = d_retur_id, F_BillPaymentDetailF_BillDpID = d_dp_id, F_BillPaymentDetailIsActive = "Y"
		WHERE F_BillPaymentDetailID = d_id;

		
        
        
	END IF;

	
	UPDATE f_bill
	SET F_BillPaid = F_BillPaid + d_amount - o_amount + d_x_change
	WHERE F_BillID = d_bill;

	
	IF d_type_code = "PAY.DP" THEN
		UPDATE f_billdp
		SET F_BillDpUsed = F_BillDpUsed + d_amount - o_amount + d_x_change
		WHERE F_BillDpID = d_dp_id;
	END IF;

	
	IF d_type_code = "PAY.RETUR" THEN
		UPDATE p_purchaseretur
		SET P_PurchaseReturUsed = P_PurchaseReturUsed + d_amount - o_amount + d_x_change
		WHERE P_PurchaseReturID = d_retur_id;
	END IF;
		

	SET n = n + 1;

	IF d_type_code="PAY.DP" THEN
	    SET total_dp = total_dp + d_amount;
	ELSEIF d_disc = "N" AND d_is_retur = "N" THEN
		SET total = total + d_amount;
    ELSEIF d_disc = "Y" AND d_is_retur ="N" THEN
        SET total_disc = total_disc + d_amount;
	ELSE
		SET total_retur = total_retur + d_amount;
    END IF;

	SET total_change = total_change + d_change;
	SET total_change_disc = total_change_disc + d_change_disc;
	SET total_change_retur = total_change_retur + d_change_retur;
	SET total_change_dp = total_change_retur + d_change_dp;
END WHILE;

SET d_ids = (SELECT CONCAT("[", GROUP_CONCAT(F_BillPaymentDetailID), "]") FROM f_billpaymentdetail WHERE F_BillPaymentDetailIsActive = "O"
    AND F_BillPaymentDetailF_BillPaymentID = paymentid);

SET n = 0;
SET l = JSON_LENGTH(d_ids);
WHILE n < l DO
	SET d_id = JSON_EXTRACT(d_ids, CONCAT("$[", n,"]"));

	SET d_bill = (SELECT F_BillPaymentDetailF_BillID FROM f_billpaymentdetail WHERE F_BillPaymentDetailID = d_id);
	SET d_retur_id = (SELECT F_BillPaymentDetailP_PurchaseReturID FROM f_billpaymentdetail WHERE F_BillPaymentDetailID = d_id);
	SET d_dp_id = (SELECT F_BillPaymentDetailF_BillDpID FROM f_billpaymentdetail WHERE F_BillPaymentDetailID = d_id);
	SET d_type_code = (SELECT M_PaymentDetailCode FROM f_billpaymentdetail JOIN m_paymentdetail ON M_PaymentDetailID = F_BillPaymentDetailM_PaymentDetailID WHERE F_BillPaymentDetailID = d_id);
	SET d_amount = (SELECT F_BillPaymentDetailAmount FROM f_billpaymentdetail WHERE F_BillPaymentDetailID = d_id);
	SET d_change = (SELECT F_BillPaymentDetailChange FROM f_billpaymentdetail WHERE F_BillPaymentDetailID = d_id);

	UPDATE f_bill
	SET F_BillPaid = F_BillPaid - d_amount
	WHERE F_BillID = d_bill;

	IF d_type_code = "PAY.DP" THEN
		UPDATE f_billdp
		SET F_BillDpUsed = F_BillDpUsed - d_amount
		WHERE F_BillDpID = d_dp_id;
	END IF;

	IF d_type_code = "PAY.RETUR" THEN
		UPDATE p_purchaseretur
		SET P_PurchaseReturUsed = P_PurchaseReturUsed - d_amount
		WHERE P_PurchaseReturID = d_retur_id;
	END IF;

	UPDATE f_billpaymentdetail SET F_BillPaymentDetailIsActive = "N" WHERE F_BillPaymentDetailID = d_id;
	SET n = n + 1;
END WHILE;

















UPDATE f_billpayment
SET F_BillPaymentTotal = total WHERe F_BillPaymentID = paymentid;

SET adata = CONCAT(JSON_OBJECT("account", account_id, "credit", total+total_change, "debit", 0),
			",", JSON_OBJECT("account", account_payable_id, "credit", 0, "debit", total+total_disc+total_retur+total_dp));

IF total_disc <> 0 THEN
	SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_disc_id, "credit", total_disc+total_change_disc, "debit", 0));
END IF;

IF total_retur <> 0 THEN
	SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_retur_id, "credit", total_retur+total_change_retur, "debit", 0));
END IF;

IF total_dp <> 0 THEN
	SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_advance_id, "credit", total_dp+total_change_dp, "debit", 0));
END IF;

IF total_change <> 0 OR total_change_disc <> 0 OR total_change_retur <> 0 OR total_change_dp <> 0 THEN
	SET adata = CONCAT(adata, ",", JSON_OBJECT("account", account_revenue_id, "credit", 0, "debit", total_change+total_change_retur+total_change_disc+total_change_dp));
END IF;

SET adata = CONCAT("[", adata, "]");


CALL sp_journal_save_notrans(journal_id, payment_date, payment_number, payment_note, adata, journal_type, account_id );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = payment_number AND T_JournalIsActive = "Y");
	UPDATE f_billpayment SET F_BillPaymentT_JournalID = journal_id WHERE F_BillPaymentID = paymentid;
END IF;

UPDATE t_journal SET T_JournalRefNote = supplier_name WHERE T_JournalID = journal_id;


SELECT "OK" as status, JSON_OBJECT("payment_id", paymentid, "total_change_disc", total_change_disc) as data;

COMMIT;

END
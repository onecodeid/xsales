BEGIN

DECLARE dp_number varchar(25);
DECLARE dp_date date;
DECLARE dp_vendor INTEGEr;
DECLARE dp_amount DOUBLE;
DECLARE dp_note VARCHAR(255);
DECLARE dp_payment_type INTEGER;
DECLARE dp_bank_account INTEGER;
DECLARE dp_bank INTEGER;
DECLARE dp_giro_date DATE;
DECLARE dp_giro_number VARCHAR(50);
DECLARE dp_transfer_date DATE;
DECLARE dp_account INTEGER;
DECLARE dp_tmp TEXT;
DECLARE dp_uuid VARCHAR(50);
DECLARE pay_id INTEGER;

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE account_id INTEGER;
DECLARE account_advance_id INTEGER;
DECLARE ptype_code VARCHAR(25);
DECLARE adata VARCHAR(5000);
DECLARE dp_acc CHAR(1);
DECLARE vendor_name VARCHAR(50);

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

SET dp_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.dp_date'));
SET dp_vendor = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.dp_vendor'));
SET dp_amount = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.dp_amount'));
SET dp_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.dp_note'));
SET dp_payment_type = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.dp_payment_type'));
SET dp_bank_account = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.dp_bank_account'));
SET dp_bank = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.dp_bank'));
SET dp_giro_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.dp_giro_date'));
SET dp_giro_number = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.dp_giro_number'));
SET dp_transfer_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.dp_transfer_date'));
SET dp_account = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.dp_account'));
SET dp_uuid = (SELECT UUID());

SET vendor_name = (SELECT M_VendorName FROM m_vendor WHERE M_VendorID = dp_vendor);



IF dp_bank IS NULL THEN SET dp_bank = 0; END IF;
IF dp_bank_account IS NULL THEN SET dp_bank_account = 0; END IF;
IF dp_giro_date IS NULL THEN SET dp_giro_date = '0000-00-00'; END IF;
IF dp_giro_number IS NULL THEN SET dp_giro_number = ''; END IF;
IF dp_transfer_date IS NULL THEN SET dp_transfer_date = '0000-00-00'; END IF;
IF dp_account IS NULL THEN SET dp_account = 0; END IF;

SET dp_tmp = JSON_OBJECT("type", dp_payment_type, "total", dp_amount, "account", dp_bank_account, "bank", dp_bank, "giro_date", dp_giro_date, "giro_number", dp_giro_number, "tdate", dp_transfer_date);

IF dpid = 0 THEN
    SET dp_number = (SELECT fn_numbering('BILLDP'));
    SET pay_id = 0;

    INSERT INTO f_billdp(
        F_BillDpNumber,
        F_BillDpDate,
        F_BillDpM_VendorID,
        F_BillDpIsGiro,
        F_BillDpAmount,
        F_BillDpUnused,
        F_BillDpNote,
        F_BillDpAcc)
    SELECT dp_number, dp_date, dp_vendor, "N", dp_amount, dp_amount, dp_note, "Y";
    SET dpid = (SELECT LAST_INSERT_ID());

    CALL sp_finance_pay_save(pay_id, dp_tmp, dp_uuid);
    SET pay_id = (SELECT F_PayID FROM f_pay WHERE F_PayUUID = dp_uuid AND F_PayIsActive = "Y" ORDER BY F_PayID DESC LIMIT 1);
    UPDATE f_billdp SET F_BillDpF_PayID = pay_id WHERE F_BillDpID = dpid;
ELSE
    SET dp_acc = (SELECT F_BillDpAcc FROM f_billdp WHERE F_BillDpID = dpid);
    IF dp_acc = "Y" THEN
        SET dp_number = (SELECT F_BillDpNumber FROM f_billdp WHERE F_BillDpID = dpid);
    ELSE
        SET dp_number = (SELECT fn_numbering('BILLDP'));
    END IF;

    UPDATE f_billdp
    SET F_BillDpDate = dp_date, F_BillDpNumber = dp_number, F_BillDpAmount = dp_amount, F_BillDpUnused = dp_amount - F_BillDpUsed, F_BillDpNote = dp_note,
        F_BillDpAcc = "Y"
    WHERE F_BillDpID = dpid;

    SET journal_id = (SELECT F_BillDpT_JournalID FROM f_billdp WHERE F_BillDpID = dpid);

    SET pay_id = (SELECT F_BillDpF_PayID FROM f_billdp WHERE F_BillDpID = dpid);
    CALL sp_finance_pay_save(pay_id, dp_tmp, dp_uuid);
END IF;

SET account_advance_id = (SELECT fn_master_get_account_id("ACC.DP"));

IF dp_account = 0 THEN
    SET ptype_code = (SELECT M_PaymentTypeCode FROM m_paymenttype WHERE M_PaymentTypeID = dp_payment_type);
    IF ptype_code = "PAY.TRANSFER" THEN
        SET dp_account = (SELECT M_AccountMapM_AccountID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapType = "PAY.02" AND M_AccountMapA_BankAccountID = dp_bank_account);
    ELSE
        SET dp_account = (SELECT M_AccountMapM_AccountID FROM m_accountmap WHERE M_AccountMapIsActive = "Y" AND M_AccountMapType <> "PAY.02" AND M_AccountMapType LIKE "PAY.%" AND M_AccountMapReffID = dp_payment_type);
    END IF;
END IF;

SET adata = JSON_ARRAY(JSON_OBJECT("account", account_advance_id, "debit", dp_amount, "credit", 0),
			JSON_OBJECT("account", dp_account, "debit", 0, "credit", dp_amount));
CALL sp_journal_save_notrans(journal_id, dp_date, dp_number, dp_note, adata, "J.16", dp_account );

IF journal_id = 0 THEN
    SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalIsActive = "Y" AND T_JournalReceipt = dp_number);

    UPDATE f_billdp SET F_BillDpT_JournalID = journal_id WHERE F_BillDpID = dpid;
END IF;

UPDATE t_journal SET T_JournalRefNote = vendor_name, T_JournalRefID = dpid WHERE T_JournalID = journal_id;

SELECT "OK" status, JSON_OBJECT("dp_id", dpid, "pay_id", pay_id) data;

COMMIT;


END
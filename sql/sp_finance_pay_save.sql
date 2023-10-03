BEGIN

DECLARE p_type INTEGER;
DECLARE p_total DOUBLE;
DECLARE p_account INTEGER;
DECLARE p_bank INTEGER;
DECLARE p_gdate DATE;
DECLARE p_gnumber VARCHAR(50);
DECLARE p_tdate DATE;
DECLARE p_uuid VARCHAR(50);

SET p_type = JSON_UNQUOTE(JSON_EXTRACT(pdata, "$.type"));
SET p_total = JSON_UNQUOTE(JSON_EXTRACT(pdata, "$.total"));
SET p_account = JSON_UNQUOTE(JSON_EXTRACT(pdata, "$.account"));
SET p_bank = JSON_UNQUOTE(JSON_EXTRACT(pdata, "$.bank"));
SET p_gdate = JSON_UNQUOTE(JSON_EXTRACT(pdata, "$.giro_date"));
SET p_gnumber = JSON_UNQUOTE(JSON_EXTRACT(pdata, "$.giro_number"));
SET p_tdate = JSON_UNQUOTE(JSON_EXTRACT(pdata, "$.tdate"));
SET p_uuid = uuid;

IF p_uuid IS NULL OR p_uuid = "" THEN SET p_uuid = (SELECT UUID()); END IF;

IF payid = 0 THEN
    INSERT INTO f_pay(
        F_PayM_PaymentTypeID,
        F_PayTotal,
        F_PayA_BankAccountID,
        F_PayM_BankID,
        F_PayGiroDate,
        F_PayGiroNumber,	
        F_PayTransferDate,
        F_PayUUID
    ) SELECT p_type, p_total, p_account, p_bank, p_gdate, p_gnumber, p_tdate, p_uuid;
    SET payid = (SELECT LAST_INSERT_ID());
ELSE
    UPDATE f_pay
    SET F_PayM_PaymentTypeID = p_type,
        F_PayTotal = p_total,
        F_PayA_BankAccountID = p_account,
        F_PayM_BankID = p_bank,
        F_PayGiroDate = p_gdate,
        F_PayGiroNumber = p_gnumber,	
        F_PayTransferDate = p_tdate
    WHERE F_PayID = payid;
END IF;

END
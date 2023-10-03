DROP PROCEDURE `sp_finance_cash_save_batch`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_cash_save_batch` (IN `hdata` text, IN `uid` integer)
BEGIN

DECLARE tmp TEXT;
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;
DECLARE cash_md5 CHAR(32);

DECLARE account_from_code VARCHAR(50);
DECLARE account_from_id INTEGER;
DECLARE account_to_code VARCHAR(50);
DECLARE account_to_id INTEGER;
DECLARE amount DOUBLE;
DECLARE jdata TEXT;

DECLARE importid INTEGER;

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

SET l = JSON_LENGTH(hdata);
WHILE n < l DO
    SET tmp = JSON_EXTRACT(hdata, CONCAT("$[", n, "]"));
    SET cash_md5 = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.cash_md5"));
    
    SET account_from_code = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.cash_from_account_code"));
    SET account_to_code = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.cash_to_account_code"));
    SET account_from_id = (SELECT M_AccountID FROM m_account WHERE M_AccountCode = account_from_code AND M_AccountIsActive = "Y" LIMIT 1);
    SET account_to_id = (SELECT M_AccountID FROM m_account WHERE M_AccountCode = account_to_code AND M_AccountIsActive = "Y" LIMIT 1);
    SET amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.cash_amount"));

    IF account_from_id IS NOT NULL AND account_to_id IS NOT NULL THEN
        SET jdata = CONCAT("[", JSON_OBJECT("account", account_from_id, "debit", 0, "credit", amount), ",", 
            JSON_OBJECT("account", account_to_id, "debit", amount, "credit", 0),"]");
        CALL sp_finance_cash_save_notrans(0, tmp, jdata, uid);
    END IF;

    SET n = n + 1;
END WHILE;

-- IMPORT LOG SECTION
INSERT INTO t_import(T_ImportDate, T_ImportNote)
SELECT date(now()), cash_md5;

SET importid = (SELECT LAST_INSERT_ID());

UPDATE t_journal
SET T_JournalT_ImportID = importid
WHERE T_JournalID IN (
    SELECT F_CashT_JournalID
    FROM f_cash WHERE F_CashMd5 = cash_md5 AND F_CashIsActive = "Y"
);
-- END OF IMPORT SECTION

SELECT "OK" as status, JSON_OBJECT("cash_cnt", l, "cash_md5", cash_md5) as data;

COMMIT;

END;;
DELIMITER ;

show columns from m_item;
show columns from one_account_aw_log.log_stock;
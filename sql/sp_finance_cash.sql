DROP PROCEDURE `sp_finance_cash_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_cash_save` (IN `cash_id` INTEGER, IN `hdata` text, IN `jdata` TEXT, IN `uid` integer)
BEGIN

DECLARE cash_number VARCHAR(25);

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

CALL sp_finance_cash_save_notrans(cash_id, hdata, jdata, uid);

IF cash_id = 0 THEN
    SET cash_id = (SELECT MAX(F_CashID) FROM f_cash WHERE F_CashIsActive = "Y");
END IF;
SET cash_number = (SELECT F_CashNumber FROM f_cash WHERE F_CashID = cash_id);

SELECT "OK" as status, JSON_OBJECT("cash_id", cash_id, "cash_number", cash_number) as data;

COMMIT;

END
DROP PROCEDURE `sp_finance_spay_delete`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_spay_delete` (IN `pay_id` int, IN `uid` int)
BEGIN

DECLARE paid_total DOUBLE;
DECLARE sales_id INTEGER;

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

SET sales_id = (SELECT F_SpayL_SalesID FROM f_spay WHERE F_SpayID = pay_id);
UPDATE f_spay SET F_SpayIsActive = "N" WHERE F_SpayID = pay_id;

SET paid_total = (SELECT SUM(F_SpayAmount) FROM f_spay WHERE F_SpayL_SalesID = sales_id AND F_SpayIsActive = "Y");
IF paid_total IS NULL THEN SET paid_total = 0; END IF;

UPDATE l_sales
SET L_SalesPaid = paid_total
WHERE L_SalesID = sales_id;

SELECT "OK" as status, JSON_OBJECT("pay_id", pay_id, "sales_id", sales_id) as data;

COMMIT;

END

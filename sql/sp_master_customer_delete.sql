BEGIN

DECLARE used INTEGER;

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

SET used = (SELECT COUNT(L_SalesID) FROM l_sales WHERE L_SalesM_CustomerID = customerid AND L_SalesIsActive = "Y");
IF used IS NULL THEN SET used = 0; END IF;

IF used = 0 THEN
    UPDATE m_customer SET M_CustomerIsActive = "N" WHERE M_CustomerID = customerid;
    SELECT "OK" status, JSON_OBJECT("customer_id", customerid) data;
    COMMIT;
ELSE
    SELECT "ERR" status, "Data Customer tersebut sudah digunakan untuk transaksi, tidak bisa dihapus :(" message;
END IF;

END
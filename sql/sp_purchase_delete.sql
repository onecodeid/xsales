DROP PROCEDURE `sp_purchase_delete`;
DELIMITER ;;
CREATE PROCEDURE `sp_purchase_delete` (IN `purchaseid` int, IN `uid` int)
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

SET used = (SELECT sum(P_PurchaseDetailReceived) FROM p_purchasedetail WHERE P_PurchaseDetailP_PurchaseID = purchaseid AND P_PurchaseDetailIsActive = "Y");
IF used > 0 THEN
    SELECT "ERR" status, "Sebagian item telah diterima, tidak bisa dihapus :(" message;
    ROLLBACK;
ELSE
    UPDATE p_purchase SET P_PurchaseIsActive = "N" WHERE P_PurchaseID = purchaseid;
    UPDATE p_purchasedetail SET P_PurchaseDetailIsActive = "N" WHERE P_PurchaseDetailP_PurchaseID = purchaseid;

    SELECT "OK" status, JSON_OBJECT("purchase_id", purchaseid) data;
    COMMIT;
END IF;

END;;
DELIMITER ;
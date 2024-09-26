DROP PROCEDURE `sp_sales_delete`;
DELIMITER ;;
CREATE PROCEDURE `sp_sales_delete` (IN `salesid` int, IN `uid` int)
BEGIN

DECLARE used INTEGER;
DECLARE offerid INTEGER;

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

SET used = (SELECT sum(L_SalesDetailSent) FROM l_salesdetail WHERE L_SalesDetailL_SalesID = salesid AND L_SalesDetailIsActive = "Y");
IF used > 0 THEN
    SELECT "ERR" status, "Sebagian item telah dikirim, tidak bisa dihapus :(" message;
    ROLLBACK;
ELSE
    UPDATE l_sales SET L_SalesIsActive = "N" WHERE L_SalesID = salesid;
    UPDATE l_salesdetail SET L_SalesDetailIsActive = "N" WHERE L_SalesDetailL_SalesID = salesid;
    UPDATE f_spay SET F_SpayIsActive = "N" WHERE F_SpayL_SalesID = salesid;

    UPDATE l_sales
    LEFT JOIN (
        SELECT F_SpayL_SalesID sales_id, SUM(F_SpayAmount) pay_amount
        FROM f_spay WHERE F_SpayIsActive = "Y" GROUP BY F_SpayL_SalesID) x ON L_SalesID = sales_id
    SET L_SalesPaid = IFNULL(pay_amount, 0);

    SET offerid = (SELECT L_SalesL_OfferID FROM l_sales WHERE L_SalesID = salesid);
    UPDATE l_offer SET L_OfferUsed = "N" WHERE L_OfferID = offerid;

    SELECT "OK" status, JSON_OBJECT("sales_id", salesid) data;
    COMMIT;
END IF;

END;;
DELIMITER ;
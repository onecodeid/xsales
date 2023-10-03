DROP PROCEDURE `sp_master_item_slow`;
DELIMITER ;;
CREATE PROCEDURE `sp_master_item_slow` (IN `itemid` int, IN `warehouseid` int, IN `val` char(1))
BEGIN

DECLARE slowid INTEGER;

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

SET slowid = (SELECT M_ItemSlowID FROM m_itemslow WHERE M_ItemSlowM_ItemID = itemid AND M_ItemSlowM_WarehouseID = warehouseid AND M_ItemSlowIsActive = "Y");
IF slowid IS NULL THEN 
    INSERT INTO m_itemslow(M_ITemSlowM_ItemID, M_ItemSlowM_WarehouseID, M_ItemSlowValue)
    SELECT itemid, warehouseid, val;

    SET slowid = (SELECT LAST_INSERT_ID()); 

ELSE
    UPDATE m_itemslow SET M_ItemSlowValue = val WHERE M_ItemSlowID = slowid;
END IF;
    
SELECT "OK" status, JSON_OBJECT("slow_id", slowid) data;
COMMIT;

END;;
DELIMITER ;
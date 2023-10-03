DROP PROCEDURE `sp_sales_delivery_confirm`;
DELIMITER ;;
CREATE PROCEDURE `sp_sales_delivery_confirm` (IN `deliveryid` int, IN `uid` int)
BEGIN

DECLARE confirm CHAR(1);
DECLARE bill INTEGER;
DECLARE fail INTEGER DEFAULT 0;
DECLARE warehouse_id INTEGER;

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

-- SELECT "OK" status, JSON_OBJECT("delivery_id", deliveryid) data;

-- COMMIT;
-- END;;
-- DELIMITER ;

SET confirm = (SELECT L_DeliveryConfirm FROM l_delivery WHERE L_DeliveryID = deliveryid);
SET bill = (SELECT L_DeliveryL_InvoiceID FROM l_delivery WHERE L_DeliveryID = deliveryid);

IF bill <> 0 THEN
    SELECT "ERR" status, "Penerimaan tersebut telah ditagihkan, tidak bisa dikonfirmasi :(" message;
    ROLLBACK;
ELSEIF confirm = "Y" THEN
    SELECT "ERR" status, "Penerimaan tersebut telah dikonfirmasi, tidak perlu diulangi :)" message;
    ROLLBACK;
ELSE
    SET warehouse_id = (SELECT L_DeliveryM_WarehouseID FROM l_delivery WHERE L_DeliveryID = deliveryid);

    SET fail = (SELECT SUM(IF(IFNULL(I_StockQty, 0) < L_DeliveryDetailQty, 1, 0))
    FROM l_deliverydetail
    LEFT JOIN i_stock ON I_StockM_ItemID = L_DeliveryDetailA_ItemID
        AND I_StockM_WarehouseID = warehouse_id
        AND I_StockIsActive = "Y"
    WHERE L_DeliveryDetailL_DeliveryID = deliveryid
        AND L_DeliveryDetailIsActive = "Y");
    IF fail IS NULL THEN SET fail = 0; END IF;

    IF fail > 0 THEN
        SELECT "ERR" status, "Ada item / produk yang memiliki stok di bawah permintaan, mohon cek kembali :(" message;
        ROLLBACK;
    ELSE
        UPDATE i_stock
        JOIN l_deliverydetail ON L_DeliveryDetailA_ItemID = I_StockM_ItemID
            AND L_DeliveryDetailL_DeliveryID = deliveryid
            AND L_DeliveryDetailIsActive = "Y"
        JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
        SET I_StockQty = I_StockQty - L_DeliveryDetailQty,
            I_StockLastTransCode = "SALES.DELIVERY",
            I_StockLastTransRefID = L_DeliveryDetailID,
            I_StockLastTransQty = (0 - L_DeliveryDetailQty),
            I_StockLastTransDate = L_DeliveryDate
        WHERE I_StockM_WarehouseID = warehouse_id
            AND I_StockIsActive = "Y";

        UPDATE l_delivery SET L_DeliveryConfirm = "Y" WHERE L_DeliveryID = deliveryid;

        SELECT "OK" status, JSON_OBJECT("delivery_id", deliveryid) data;
        COMMIT;
    END IF;

    
END IF;

END;;
DELIMITER ;